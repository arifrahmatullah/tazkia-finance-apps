import './bootstrap';

window.initSelect2 = function (scope = document) {
    $(scope).find('select:not(.no-select2):not(.select2-hidden-accessible)').select2({
        width: '100%',
        minimumResultsForSearch: 0,
        language: {
            noResults:  () => 'Tidak ditemukan',
            searching:  () => 'Mencari...',
        },
    });
};

$(document).ready(function () {
    window.initSelect2();
});
