import './bootstrap';

/**
 * Menu toggle
 */

const $mainNav = document.querySelector('#dropdown');
const $dropdownToggle = document.querySelector('#dropdown-toggle');

if ($dropdownToggle) {
    $dropdownToggle.addEventListener('click', function (event) {
        $mainNav.classList.toggle('hidden');

        if ($mainNav.classList.contains('hidden')) {
            $dropdownToggle.querySelector('[data-type="burger"]').classList.remove('hidden');
            $dropdownToggle.querySelector('[data-type="close"]').classList.add('hidden');
        } else {
            $dropdownToggle.querySelector('[data-type="burger"]').classList.add('hidden');
            $dropdownToggle.querySelector('[data-type="close"]').classList.remove('hidden');
        }
    });
}
