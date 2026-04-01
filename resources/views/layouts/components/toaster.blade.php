@if(Session::has('alert_msg'))
<div class="toast-container position-fixed top-0 end-0 p-3">
    <div id="toast-wrapper" class="toast colored-toast bg-{{ Session::get('alert_class') }}-transparent fade show" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header bg-{{ Session::get('alert_class') }} text-fixed-white">
            <strong class="me-auto">Leadgen</strong>
            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body">
            {{ Session::get('alert_msg') }}
        </div>
    </div>
</div>

<script>
    setTimeout(() => {
        $('#toast-wrapper').removeClass('show');
        $('#toast-wrapper').addClass('hide');
    }, 2000);
</script>
@endif
