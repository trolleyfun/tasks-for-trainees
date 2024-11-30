$(document).ready(function() {
    /* Функция добавляет/убирает выделение со всех объектов на странице */
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

    $('#create-folder').submit(function(e) {
        e.preventDefault();

        $.ajax({
            url: $(this).attr('action'),
            method: 'post',
            dataType: 'json',
            data: $(this).serialize(),
            success: function(data) {
                //location.reload();
                console.log(data.message);
            },
            error: function() {
                console.log('Error');
            }
        });
    });
});
