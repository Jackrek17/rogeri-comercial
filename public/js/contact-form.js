(function ($, axios) {
  "use strict";

  const $form = $("#contact-form");
  const $feedback = $("#contact-feedback");

  if ($form.length === 0 || $feedback.length === 0) {
    return;
  }

  const REQUIRED_FIELDS = ["name", "company", "whatsapp", "email", "project_volume"];
  const FIELD_LABELS = {
    name: "Nome",
    company: "Empresa / Construtora",
    whatsapp: "WhatsApp",
    email: "Email",
    project_volume: "Volume da Obra",
    message: "Mensagem",
  };

  function getFieldSelector(fieldName) {
    return "[name='" + fieldName + "']";
  }

  function getFieldLabel(fieldName) {
    return FIELD_LABELS[fieldName] || fieldName;
  }

  function getErrorElement($field) {
    let $error = $field.closest(".flex").find(".field-error");
    if ($error.length === 0) {
      $error = $("<p>")
        .addClass("field-error text-xs text-red-400 mt-1 min-h-[1rem]")
        .insertAfter($field);
    }

    return $error;
  }

  function setFieldError(fieldName, message) {
    const $field = $form.find(getFieldSelector(fieldName));
    if ($field.length === 0) {
      return;
    }

    $field.addClass("input-error").attr("aria-invalid", "true");
    getErrorElement($field).text(message);
  }

  function clearFieldError(fieldName) {
    const $field = $form.find(getFieldSelector(fieldName));
    if ($field.length === 0) {
      return;
    }

    $field.removeClass("input-error").removeAttr("aria-invalid");
    const $error = $field.closest(".flex").find(".field-error");
    if ($error.length) {
      $error.text("");
    }
  }

  function clearAllFieldErrors() {
    $form.find(".input-error").removeClass("input-error").removeAttr("aria-invalid");
    $form.find(".field-error").text("");
  }

  function setFeedback(message, type) {
    const baseClass = "md:col-span-2 text-sm";
    const toneClass =
      type === "success"
        ? "text-green-500"
        : type === "loading"
          ? "text-base-content"
          : "text-red-500";

    $feedback.attr("class", baseClass + " " + toneClass).text(message);
  }

  function validateRequiredFields() {
    const errors = {};

    REQUIRED_FIELDS.forEach(function (fieldName) {
      const value = String($form.find(getFieldSelector(fieldName)).val() || "").trim();
      if (value === "") {
        errors[fieldName] = getFieldLabel(fieldName) + " é obrigatório.";
      }
    });

    return errors;
  }

  function validateFieldFormats() {
    const errors = {};
    const email = String($form.find("[name='email']").val() || "").trim();
    const whatsapp = String($form.find("[name='whatsapp']").val() || "").trim();
    const message = String($form.find("[name='message']").val() || "").trim();

    if (email !== "" && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
      errors.email = "Email inválido.";
    }

    if (whatsapp !== "" && !/^\+?[0-9\s\-()]{10,20}$/.test(whatsapp)) {
      errors.whatsapp = "WhatsApp inválido.";
    }

    if (message !== "" && message.length < 5) {
      errors.message = "Mensagem deve ter ao menos 5 caracteres.";
    }

    return errors;
  }

  function mergeErrors() {
    return $.extend({}, validateRequiredFields(), validateFieldFormats());
  }

  function applyErrors(errors) {
    Object.keys(errors).forEach(function (fieldName) {
      setFieldError(fieldName, errors[fieldName]);
    });
  }

  function setFormLoading(isLoading) {
    const $submitButton = $form.find("button[type='submit']");
    $submitButton.prop("disabled", isLoading).text(isLoading ? "Enviando..." : "Enviar");
  }

  function extractPayload(responseData) {
    if (responseData && typeof responseData === "object") {
      return responseData;
    }

    return { ok: false, message: "Resposta inválida do servidor." };
  }

  function getAxiosErrorPayload(error) {
    const hasResponse = error && error.response;
    if (!hasResponse) {
      return {
        ok: false,
        message: "Erro de conexão ao enviar o formulário.",
      };
    }

    if (typeof error.response.data === "object" && error.response.data !== null) {
      return error.response.data;
    }

    return {
      ok: false,
      message:
        "Servidor retornou uma resposta inválida. Verifique se o backend PHP está ativo.",
    };
  }

  function bindFieldValidation() {
    $form.on("input change", "input, select, textarea", function () {
      const fieldName = $(this).attr("name");
      if (!fieldName) {
        return;
      }

      clearFieldError(fieldName);
    });
  }

  function maskWhatsapp(value) {
    const digits = String(value || "").replace(/\D/g, "").slice(0, 11);

    if (digits.length <= 2) {
      return digits.length ? "(" + digits : "";
    }

    if (digits.length <= 6) {
      return "(" + digits.slice(0, 2) + ") " + digits.slice(2);
    }

    if (digits.length <= 10) {
      return (
        "(" +
        digits.slice(0, 2) +
        ") " +
        digits.slice(2, 6) +
        "-" +
        digits.slice(6)
      );
    }

    return (
      "(" +
      digits.slice(0, 2) +
      ") " +
      digits.slice(2, 7) +
      "-" +
      digits.slice(7)
    );
  }

  function bindWhatsappMask() {
    const $whatsapp = $form.find("[name='whatsapp']");
    if ($whatsapp.length === 0) {
      return;
    }

    $whatsapp.on("input", function () {
      $(this).val(maskWhatsapp($(this).val()));
    });
  }

  function submitForm() {
    clearAllFieldErrors();

    const clientErrors = mergeErrors();
    if (Object.keys(clientErrors).length > 0) {
      applyErrors(clientErrors);
      setFeedback("Preencha os campos obrigatórios para continuar.", "error");
      return;
    }

    setFeedback("Enviando...", "loading");
    setFormLoading(true);

    axios
      .post($form.attr("action"), new FormData($form[0]))
      .then(function (response) {
        const payload = extractPayload(response.data);

        if (!payload.ok) {
          const serverErrors = payload.errors || {};
          applyErrors(serverErrors);
          setFeedback(payload.message || "Erro ao enviar.", "error");
          return;
        }

        setFeedback(payload.message || "Formulário enviado com sucesso.", "success");
        $form.trigger("reset");
        clearAllFieldErrors();
      })
      .catch(function (error) {
        const payload = getAxiosErrorPayload(error);
        const serverErrors = payload.errors || {};
        applyErrors(serverErrors);
        setFeedback(payload.message || "Erro ao enviar.", "error");
      })
      .finally(function () {
        setFormLoading(false);
      });
  }

  function init() {
    bindWhatsappMask();
    bindFieldValidation();

    $form.on("submit", function (event) {
      event.preventDefault();
      submitForm();
    });
  }

  init();
})(window.jQuery, window.axios);
