<!-- Footer Start -->
<footer class="footer">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12 text-center">
                {{date('Y')}} © <b>{{ getSetting('site_title') ? getSetting('site_title') : config('app.name') }}</b> All rights reserved.
            </div>
        </div>
    </div>
</footer>
<!-- end Footer -->