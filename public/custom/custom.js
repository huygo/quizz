const callAjax = async (url, dataPost, typeData, method, csrfToken, check_loading)=>{
    jQuery('#loading_spinner').remove();
    jQuery('#notify_model').remove();
    // add modal bootstrap loading
    var html_spiner = '<div class="modal fade" id="loading_spinner" data-backdrop="static" data-keyboard="false">\
        <div class="modal-dialog modal-dialog-centered">\
            <div class="modal-body">\
            <center><div class="lds-ring"><div></div><div></div><div></div><div></div></div></center>\
            </div>\
        </div>\
    </div>';
    
    $('body').append(html_spiner);
    try {
        const result = await $.ajax({
            type: method,
            url: url,
            dataType: typeData,
            data: dataPost,
            headers: { 'X-CSRF-Token': csrfToken },
            beforeSend: function () {
                if(check_loading == 1){
                    $('#loading_spinner').modal('show');
                }
            },
            success: function (response) {
                if (typeData == 'json') {
                    if (response.status == 100000) {
                        var Url = window.location.origin;
                        $(location).attr('href', Url+'/api-laravel/public/logout');
                        return false;
                    }
                }else{
                    if (response == '{"status":100000}') {
                        var Url = window.location.origin;
                        $(location).attr('href', Url+'/api-laravel/public/logout');
                        return false;
                    }
                }
            },
            complete: function () {
                $('#loading_spinner').modal('hide');
            },
            fail: function() {
                $('#loading_spinner').modal('hide');
            }
        });
        return result;
    } catch (e) {
        return false;
    }
}



function notifyModel(option = false, context = ''){
    var element;
    if(option == true){
        element = '<input id = "update_info-contracting" type="button" class="btn btn-primary" value="'+context+'" data-dismiss="modal"/>';
    }else{
        element = '<input type="button" class="btn btn-primary" value="Đóng" data-dismiss="modal" aria-label="Close"/>';
    }
    var notifyModel = '<div class="modal fade" id="notify_model">\
    <div class="modal-dialog">\
      <div class="modal-content">\
        <div class="modal-header" style="justify-content: center">\
        <h4 class="modal-title">Thông Báo</h4>\
        </div>\
        <div class="modal-body">\
            <center id="notify"></center>\
        </div>\
        <div class="modal-footer justify-content-between btn-notify" style="justify-content: center !important;">\
         <button type="button" class="btn btn-success" data-dismiss="modal">Đóng</button>\
        </div>\
      </div>\
    </div>\
  </div>';

    $('body').append(notifyModel);
    $('#loading_spinner').modal('hide');
    $('#notify_model').modal('show');
}