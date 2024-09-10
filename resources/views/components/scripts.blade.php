    <!-- jQuery 3 -->
    <!-- <script src="https://code.jquery.com/jquery-3.7.0.js" ></script> -->

    <script src="bower_components/jquery/dist/jquery.min.js"></script>
    <script type="text/javascript" src="https://code.jquery.com/jquery-3.7.1.js"></script>
    <!-- jQuery UI 1.11.4 -->
    <script src="bower_components/jquery-ui/jquery-ui.min.js"></script>
    <!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
    <script>
        $.widget.bridge('uibutton', $.ui.button);
    </script>
    <!-- Bootstrap 3.3.7 -->
    <script src="bower_components/bootstrap/dist/js/bootstrap.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js" ></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js" ></script>
        <script src="{{ asset('/vendor/datatables/buttons.server-side.js') }}"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js" ></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js" ></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js" ></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js" ></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js" ></script>

    <!-- Morris.js charts -->
    <script src="bower_components/raphael/raphael.min.js"></script>
    <script src="bower_components/morris.js/morris.min.js"></script>
    <!-- Sparkline -->
    <script src="bower_components/jquery-sparkline/dist/jquery.sparkline.min.js"></script>
    <!-- jvectormap -->
    <script src="plugins/jvectormap/jquery-jvectormap-1.2.2.min.js"></script>
    <script src="plugins/jvectormap/jquery-jvectormap-world-mill-en.js"></script>
    <!-- jQuery Knob Chart -->
    <script src="bower_components/jquery-knob/dist/jquery.knob.min.js"></script>
    <!-- daterangepicker -->
    <script src="bower_components/moment/min/moment.min.js"></script>
    <script src="bower_components/bootstrap-daterangepicker/daterangepicker.js"></script>
    <!-- datepicker -->
    <script src="bower_components/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js"></script>
    <!-- Bootstrap WYSIHTML5 -->
    <script src="plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.all.min.js"></script>
    <!-- Slimscroll -->
    <script src="bower_components/jquery-slimscroll/jquery.slimscroll.min.js"></script>
    <!-- FastClick -->
    <script src="bower_components/fastclick/lib/fastclick.js"></script>
    
    <script src="plugins/dataTables/datatables.min.js"></script>
    <script src="plugins/dataTables/dataTables.bootstrap4.min.js"></script>
    <!-- Select2 -->
    <script src="bower_components/select2/dist/js/select2.full.min.js"></script>
    <script src="plugins/toastr/toastr.min.js"></script>
    
    <!-- AdminLTE App -->
    <script src="dist/js/adminlte.min.js"></script>
    <!-- AdminLTE for demo purposes -->
    {{--<script src="dist/js/demo.js"></script>--}}
    <!-- <script src="https://cdn.datatables.net/buttons/1.0.3/js/dataTables.buttons.min.js"></script> -->

    

    
    <div id="footer"></div>

    <!-- <script type="text/javascript">
        var script = document.createElement('script');
        script.type='text/javascript';
        var myEle = document.getElementById("example2");
        console.log('myEle')
        console.log(myEle)

        if(myEle == null) {
          script.src = "<?php echo asset('/vendor/datatables/buttons.server-side.js'); ?>";
        }
        document.getElementsByTagName("head")[0].appendChild(script);
        document.getElementById('footer').appendChild(script);
    </script> -->
    <script src="plugins/iCheck/icheck.min.js"></script>

    <!-- <script src="https://cdn.datatables.net/2.0.5/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.0.1/js/dataTables.buttons.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.0.1/js/buttons.dataTables.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.0.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js" ></script> -->



    <script type="text/javascript" src="https://cdn.datatables.net/buttons/1.6.5/js/buttons.bootstrap4.min.js"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            $('#example2').DataTable( {
                pageLength : 25,
                order: [],
                dom: 'Bfrtip',
                buttons: [
                    'copy', 'csv', 'excel', 'pdf', 'print'
                ]
            } );
        } );
    </script>
    <script type="text/javascript">
        $(document).on('click','.delete-row',function(){
            if(confirm('Are you sure to delete this record?')){
                $(this).parent('form').submit();
            }
        });
        $(document).ready(function(){
            $('.datepicker').datepicker({
                format: 'dd-mm-yyyy'
            });
            $('.select2').select2();
            toastr.options = {
                closeButton: true,
                debug: false,
                progressBar: true,
                preventDuplicates: true,
                hideDuration: 800,
                showDuration: 300,
                extendedTimeOut: 4000,
                positionClass: 'toast-top-right',
            };
            @if(session()->has('success'))
            @php
                $message = explode('|',session('success'));
            @endphp
            toastr.success('{{ $message[1] }}','{{ $message[0] }}')
            @elseif(session()->has('error'))
            @php
                $message = explode('|',session('error'));
            @endphp
            toastr.error('{{ $message[1] }}','{{ $message[0] }}')
            @endif
        });
    </script>
    <script>
        $(function () {
            
            $('input').iCheck({
                checkboxClass: 'icheckbox_square-blue',
                radioClass: 'iradio_square-blue',
                increaseArea: '20%' /* optional */
            });
            
            $('#chataavailability').change(function(){
                alert("jnk"); 
            });
            
        });
    </script>
    <script>
        let route = "{{ url('/') }}";
    </script>
    @section('scripts')
    
    @show

    @yield('javascripts')