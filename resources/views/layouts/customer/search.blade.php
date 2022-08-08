<div class="card">
    <div class="card-header">
        <h3 class="card-title">Danh sách khách hàng</h3>
        <!-- <button type="button" class="btn btn-success float-right" style="margin-right: 5px;">
                    <i class="fas fa-download"></i>  Xuất excel
        </button> -->
        <div class="btn-group show  float-right">
            <button type="button" class="btn btn-success"><i class="fas fa-download"></i>  Xuất excel</button>
            <button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown" aria-expanded="true">
                <span class="sr-only">Toggle Dropdown</span>
            </button>
            <div class="dropdown-menu" role="menu">
                <a class="dropdown-item export" href="#">Xuất trang hiện tại</a>
                <a class="dropdown-item export-all" href="#">Xuất tất cả các trang</a>
            </div>
        </div>
    </div>
    <!-- /.card-header -->
    
    <div class="card-body table-responsive p-0">
        
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th style="width: 10px">#</th>
                    <th>Name</th>
                    <th>Điện thoại</th>
                    <th>Email</th>
                    <th>Registrar</th>
                </tr>
            </thead>
            <tbody>
                <?php $i= $page['from']; ?>
                @foreach($data as $khachhang)
                <tr>
                    <td>{{$i++}}</td>
                    <td>{{ $khachhang["name"] }}</td>
                    <td>{{ $khachhang["phone"] }}</td>
                    <td>{{ $khachhang["email"] }}</td>
                    <td>{{ $khachhang["registrar"] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    

    <!-- /.card-body -->
    <div class="card-footer clearfix page_number">
        <div class="form-group pagination pagination-sm m-0 float-left">
        <select class="custom-select" id="record">
            <option value="25" <?php if ($page['per_page']==25) { echo "selected"; } ?>>25</option>
            <option value="50" <?php if ($page['per_page']==50) { echo "selected"; } ?>>50</option>
            <option value="100" <?php if ($page['per_page']==100) { echo "selected"; } ?>>100</option>
        </select>
        </div>
        <ul class="pagination pagination-sm m-0 float-right">
            @if($page['current_page']>1 && $page['last_page']>1)
            <li class="page-item"><a class="page-link" href="#">&laquo;</a></li>
            @endif
            @for ($i = 1; $i <= 5; $i++)
            <li class="page-item">
                <a class="page-link" href="#">{{ $i }}</a>
            </li>
            @endfor
            @if($page['current_page'] < $page['last_page'])
            <li class="page-item"><a class="page-link" href="#">&raquo;</a></li>
            @endif
        </ul>
    </div>
</div>

<input type="hidden" value="{{ $page['last_page'] }}" id="page_count" />
<input type="hidden" value="{{ $page['current_page'] }}" id="page" />

<div class="modal fade" id="export"  role="dialog" aria-labelledby="exampleModalLongTitle" aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
        	<div class="modal-header" style="justify-content: center;">
        		 <h2 class="title_popup" id="exampleModalLongTitle">DOWNLOAD EXCEL</h2>
        	</div>
        	<div class="modal-body" id="content-load">
              
        		<div class="progress" style="display: none;">
				    <div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="40" aria-valuemin="0" aria-valuemax="100" style="width:0%">
				        0%
				    </div>
			    </div>
        	</div>
        	<div class="modal-footer">
        		<p style="text-align: center; color: red;">Quá trình download file excel đang được thực thi, vui lòng đợi trong giây lát!</p>
        	</div>
    	</div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        $(".page_number ul li a").click(function (event) {
            event.preventDefault();
            var record = $("#record").val() != "" ? $("#record").val() : 25;
            var now_page = $("#page").val();
            var csrfToken = $('input[name="_token"]').val();
            var name = ($('#name').val() != '') ? $('#name').val() : null;
            var phone = ($('#phone').val() != '') ? $('#phone').val() : null;
            page = $(this).text();
            if (page == "«" || page == "First") {
                page = 1;
            } else if (page == "»" || page == "Last") {
                page = $("#page_count").val();
            } else {
                page = $(this).text();
            }
            if (page == now_page) {
                return false;
            }
            var url = "{{ route('customer.search') }}";
            var dataPost = {
                page: page,
                record: record,
                name:name,
                phone:phone
            };
            var type = "html";
            var method = "POST";
            callAjax(url, dataPost, type, method, csrfToken, true).then(
                (response) => {
                    $("#content").html(response);
                }
            );
        });

        $("#record").change(function () {
            var record = $(this).val();
            var now_page = $("#page").val();
            var csrfToken = $('input[name="_token"]').val();
            var name = ($('#name').val() != '') ? $('#name').val() : null;
            var phone = ($('#phone').val() != '') ? $('#phone').val() : null;
            var url = "customer/search";
            var dataPost = {
                page: now_page,
                record: record,
                name:name,
                phone:phone
            };
            var type = "html";
            var method = "POST";
            callAjax(url, dataPost, type, method, csrfToken, 1).then(response=>{
                    $("#content").html(response);
                    $("#record>[value=" + record + "]").attr("selected", true);
            });
        });

        $(".export").click(function () {
            $('#export').modal('show');
			$('.progress').show();
            $('.progress-bar').css('width', '0%'); 
            var dataOutput = [];
            var pageNumber = $('#page').val();
            var totalPages = pageNumber;
            exportExcel(totalPages, dataOutput, pageNumber);
        });

        $('.export-all').click(function() {
			$('#export').modal('show');
			$('.progress').show();
            $('.progress-bar').css('width', '0%'); 
            var totalPages = $('#page_count').val();
            var dataOutput = [];
            var pageNumber = 1;
            exportExcel(totalPages, dataOutput, pageNumber);
		});
    });

    function exportExcel(totalPages, dataOutput, pageNumber){
            var record = ($('#record').val() != '') ? $('#record').val() : null;
            var csrfToken = $('input[name="_token"]').val();
            var name = ($('#name').val() != '') ? $('#name').val() : null;
            var phone = ($('#phone').val() != '') ? $('#phone').val() : null;
            var url = "customer/excel";
            var dataPost = {
                page : pageNumber,
                record : record,
                name:name,
                phone:phone
            };
            var type = 'json';
            var method = 'POST';
            callAjax(url, dataPost, type, method, csrfToken, 0).then(res => {
                if (res.status == 1) {
                    if (typeof pageNumber != 'undefined' && typeof totalPages != 'undefined') {
                        $('.progress-bar').css('width', Math.ceil((pageNumber * 100 / totalPages)) + '%');
                        $('.progress-bar').html(Math.ceil((pageNumber * 100 / totalPages)) + '%');
                        dataOutput = dataOutput.concat(res.itemsExcel);
                        pageNumber ++;
                        if (pageNumber > totalPages) {
                            $('#export').modal('hide');
                            $('.progress').hide();
                            $('.progress-bar').css('width', '0%');
                            $('.progress-bar').html('0%');
                            alasql('SELECT * INTO XLSX("danh_sach_khach_hang.xlsx",{headers:true, type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet;charset=base64"}) FROM ?', [dataOutput]);
                        }else {
                            exportExcel(totalPages,dataOutput,pageNumber);                        
                        }
                    }else {
                        $('#export').modal('hide');
                        $('.progress').hide();
                        notifyModel(); 
                        $('#notify').html('Xuất dữ liệu ra excel không thành công.');
                        $('#notify').css('color','red');
                    }
                }else {
                    $('#export').modal('hide');
                    $('.progress').hide();
                    notifyModel(); 
                    $('#notify').html('Xuất dữ liệu ra excel không thành công');
                    $('#notify').css('color','red');
                }
            });
        }
</script>
