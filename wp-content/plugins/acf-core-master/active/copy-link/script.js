document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.copy-permalink__btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var permalink = btn.getAttribute('data-permalink');
            var tooltip   = btn.querySelector('.copy-permalink__tooltip');

            navigator.clipboard.writeText(permalink).then(function () {
                tooltip.textContent = 'Copied!';
                btn.classList.add('copy-permalink__btn--copied');

                setTimeout(function () {
                    tooltip.textContent = '';
                    btn.classList.remove('copy-permalink__btn--copied');
                }, 2000);
            }).catch(function () {
                // Fallback for older browsers
                var ta = document.createElement('textarea');
                ta.value = permalink;
                ta.style.position = 'fixed';
                ta.style.opacity  = '0';
                document.body.appendChild(ta);
                ta.focus();
                ta.select();
                document.execCommand('copy');
                document.body.removeChild(ta);

                tooltip.textContent = 'Copied!';
                btn.classList.add('copy-permalink__btn--copied');

                setTimeout(function () {
                    tooltip.textContent = '';
                    btn.classList.remove('copy-permalink__btn--copied');
                }, 2000);
            });
        });
    });
});
