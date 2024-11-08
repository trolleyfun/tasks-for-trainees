/* Функция добавляет/убирает выделение со всех объектов на странице */
$(document).ready(function() {
    $('#select-all').click(function(e) {
        e.preventDefault();
        $('.checkbox-item').each(function() {
            this.checked = true;
        });
    });

    $('#unselect-all').click(function(e) {
        e.preventDefault();
        $('.checkbox-item').each(function() {
            this.checked = false;
        });
    });
});
