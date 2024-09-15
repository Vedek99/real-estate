jQuery(document).ready(function($) {
    function loadPosts(page = 1) {
        var formData = $('.real-estate-filter').serialize(); 

        $.ajax({
            url: realEstateAjax.ajaxurl,
            type: 'GET',
            data: formData + '&action=filter_real_estate&page=' + page,
            beforeSend: function() {
                $('.real-estate__container').html('<p>Завантаження результатів...</p>');
            },
            success: function(response) {
                $('.real-estate__container').html(response.html); 

                
                $('.pagination').html(response.pagination);

                
                $('.pagination a').each(function() {
                    var href = $(this).attr('href');
                    $(this).attr('href', href + '&action=filter_real_estate');
                });
            },
            error: function() {
                $('.real-estate__container').html('<p>Сталася помилка. Спробуйте ще раз.</p>');
            }
        });
    }

    
    $('.real-estate-filter').on('submit', function(e) {
        e.preventDefault();
        loadPosts(); 
    });

    
    $(document).on('click', '.pagination a', function(e) {
        e.preventDefault();
        var page = $(this).attr('href').split('page=')[1]; 
        loadPosts(page); 
    });

    loadPosts();
});