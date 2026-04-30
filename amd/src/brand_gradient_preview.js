define([], function() {
    const normalize = (value) => {
        value = (value || '').trim();
        if (!value) {
            return '#a855f7';
        }
        if (value.charAt(0) !== '#') {
            value = '#' + value;
        }
        return value;
    };

    const buildGradient = (color) => {
        const endcolor = normalize(color);
        return `linear-gradient(135deg, #f5f7fb 0%, #e6e9f5 35%, #c9c3f5 65%, ${endcolor} 100%)`;
    };

    const init = (inputSelector, previewSelector) => {
        const input = document.querySelector(inputSelector);
        const preview = document.querySelector(previewSelector);

        if (!input || !preview) {
            return;
        }

        const update = () => {
            const gradient = buildGradient(input.value);
            preview.style.background = gradient;
            preview.dataset.gradient = gradient;
        };

        update();
        input.addEventListener('input', update);
        input.addEventListener('change', update);
    };

    return {
        init: init
    };
});
