const mix = require('laravel-mix');

mix.disableSuccessNotifications();

mix
    .setPublicPath('public/')
    .options({ processCssUrls: false })
    .copy('resources/graindashboard/gd-icons', 'public/graindashboard/css')
    .sass('resources/graindashboard/sass/graindashboard.scss', 'public/graindashboard/css', {
        sassOptions: { outputStyle: 'compressed' }
    })
    .js('resources/graindashboard/js/graindashboard.js', 'public/graindashboard/js')
    .scripts([
        'resources/graindashboard/js/components/gd.malihu-scrollbar.js',
        'resources/graindashboard/js/components/gd.side-nav.js',
        'resources/graindashboard/js/components/gd.unfold.js',
    ], 'public/graindashboard/js/graindashboard.vendor.js')
    .copy('node_modules/datatables.net/js/dataTables.min.js', 'public/vendor/datatables/dataTables.min.js')
    .copy('node_modules/datatables.net-bs4/js/dataTables.bootstrap4.min.js', 'public/vendor/datatables/dataTables.bootstrap4.min.js')
    .copy('node_modules/datatables.net-bs4/css/dataTables.bootstrap4.min.css', 'public/vendor/datatables/dataTables.bootstrap4.min.css')
    .version();
