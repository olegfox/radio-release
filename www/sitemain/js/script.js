function checkTime() {
    var hour = (new Date()).getHours();

    if(hour >= 21 || (hour >= 0 && hour < 9) ) {
        $('body').removeClass('dark');
    } else {
        $('body').addClass('dark');
    }

    setTimeout(function() {
        checkTime();
    }, 5000);
}

$(function () {
    $(".email").colorbox({
        html: $(".email-form").html(),
        onComplete: function(){
            $("#cboxContent form").validate({
                messages: {
                    "site_mainbundle_email[name]": "Введите имя!",
                    "site_mainbundle_email[email]": "Введите email!"
                },
                showErrors: function(errorMap, errorList) {
                    this.defaultShowErrors();
                    $.colorbox.resize();
                },
                submitHandler: function(form) {
                    $(form).ajaxSubmit({
                        beforeSubmit: function(arr, $form, options) {
                            $("#cboxLoadedContent").html('<div class="alert alert-success" role="alert">Сообщение успешно отправлено!</div>');
                            $.colorbox.resize();
                        }
                    });
                }
            });
        }
    });
})
;

checkTime();