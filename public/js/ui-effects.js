(function ($) {
  "use strict";

  const $revealItems = $(".reveal");
  if ($revealItems.length === 0) {
    return;
  }

  $revealItems.each(function (index) {
    this.style.transitionDelay = (index % 6) * 55 + "ms";
  });

  if (!("IntersectionObserver" in window)) {
    $revealItems.addClass("is-visible");
    return;
  }

  const observer = new IntersectionObserver(
    function (entries, obs) {
      entries.forEach(function (entry) {
        if (!entry.isIntersecting) {
          return;
        }

        entry.target.classList.add("is-visible");
        obs.unobserve(entry.target);
      });
    },
    {
      threshold: 0.15,
      rootMargin: "0px 0px -6% 0px",
    }
  );

  $revealItems.each(function () {
    observer.observe(this);
  });
})(window.jQuery);
