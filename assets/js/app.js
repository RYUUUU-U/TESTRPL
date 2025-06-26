$(document).ready(function($) {

    // Variables declarations
    var $wrapper = $('.main-wrapper');
    var $pageWrapper = $('.page-wrapper');
    var $slimScrolls = $('.slimscroll');
    var $sidebarOverlay = $('.sidebar-overlay');

    // Sidebar
    var Sidemenu = function() {
        this.$menuItem = $('#sidebar-menu a');
    };

    function init() {
        var $this = Sidemenu;
        $('#sidebar-menu a').on('click', function(e) {
            if ($(this).parent().hasClass('submenu')) {
                e.preventDefault();
            }
            if (!$(this).hasClass('subdrop')) {
                $('ul', $(this).parents('ul:first')).slideUp(350);
                $('a', $(this).parents('ul:first')).removeClass('subdrop');
                $(this).next('ul').slideDown(350);
                $(this).addClass('subdrop');
            } else if ($(this).hasClass('subdrop')) {
                $(this).removeClass('subdrop');
                $(this).next('ul').slideUp(350);
            }
        });
        $('#sidebar-menu ul li.submenu a.active').parents('li:last').children('a:first').addClass('active').trigger('click');
    }
    // Sidebar Initiate
    init();

    // Sidebar overlay
    function sidebar_overlay($target) {
        if ($target.length) {
            $target.toggleClass('opened');
            $sidebarOverlay.toggleClass('opened');
            $('html').toggleClass('menu-opened');
            $sidebarOverlay.attr('data-reff', '#' + $target[0].id);
        }
    }

    // Mobile menu sidebar overlay
    $(document).on('click', '#mobile_btn', function() {
        var $target = $($(this).attr('href'));
        sidebar_overlay($target);
        $wrapper.toggleClass('slide-nav');
        $('#chat_sidebar').removeClass('opened');
        return false;
    });

    // Chat sidebar overlay
    $(document).on('click', '#task_chat', function() {
        var $target = $($(this).attr('href'));
        console.log($target);
        sidebar_overlay($target);
        return false;
    });

    // Sidebar overlay reset
    $sidebarOverlay.on('click', function() {
        var $target = $($(this).attr('data-reff'));
        if ($target.length) {
            $target.removeClass('opened');
            $('html').removeClass('menu-opened');
            $(this).removeClass('opened');
            $wrapper.removeClass('slide-nav');
        }
        return false;
    });

    // Select 2
    if ($('.select').length > 0) {
        $('.select').select2({
            minimumResultsForSearch: -1,
            width: '100%'
        });
    }

    // Floating Label
    if ($('.floating').length > 0) {
        $('.floating').on('focus blur', function(e) {
            $(this).parents('.form-focus').toggleClass('focused', (e.type === 'focus' || this.value.length > 0));
        }).trigger('blur');
    }

    // Left Sidebar Scroll
    if ($slimScrolls.length > 0) {
        $slimScrolls.slimScroll({
            height: 'auto',
            width: '100%',
            position: 'right',
            size: '7px',
            color: '#ccc',
            wheelStep: 10,
            touchScrollStep: 100
        });
        var wHeight = $(window).height() - 60;
        $slimScrolls.height(wHeight);
        $('.sidebar .slimScrollDiv').height(wHeight);
        $(window).resize(function() {
            var rHeight = $(window).height() - 60;
            $slimScrolls.height(rHeight);
            $('.sidebar .slimScrollDiv').height(rHeight);
        });
    }

    // Page wrapper height
    var pHeight = $(window).height();
    $pageWrapper.css('min-height', pHeight);
    $(window).resize(function() {
        var prHeight = $(window).height();
        $pageWrapper.css('min-height', prHeight);
    });

    // Datetimepicker
    if ($('.datetimepicker').length > 0) {
        $('.datetimepicker').datetimepicker({
            format: 'YYYY-MM-DD'
        });
    }
    if ($('#datetimepicker3').length > 0) {
        $('#datetimepicker3').datetimepicker({
            format: 'HH:mm'
        });
    }
    if ($('#datetimepicker4').length > 0) {
        $('#datetimepicker4').datetimepicker({
            format: 'HH:mm'
        });
    }


    // Datatable
    if ($('.datatable').length > 0) {
        $('.datatable').DataTable({
            "bFilter": false,
        });
    }

    // Bootstrap Tooltip
    if ($('[data-toggle="tooltip"]').length > 0) {
        $('[data-toggle="tooltip"]').tooltip();
    }

    // Mobile Menu
    $(document).on('click', '#open_msg_box', function() {
        $wrapper.toggleClass('open-msg-box');
        return false;
    });

    // Small Sidebar
    if (screen.width >= 992) {
        $(document).on('click', '#toggle_btn', function() {
            if ($('body').hasClass('mini-sidebar')) {
                $('body').removeClass('mini-sidebar');
                $('.subdrop + ul').slideDown();
            } else {
                $('body').addClass('mini-sidebar');
                $('.subdrop + ul').slideUp();
            }
            return false;
        });
        $(document).on('mouseover', function(e) {
            e.stopPropagation();
            if ($('body').hasClass('mini-sidebar') && $('#toggle_btn').is(':visible')) {
                var targ = $(e.target).closest('.sidebar').length;
                if (targ) {
                    $('body').addClass('expand-menu');
                    $('.subdrop + ul').slideDown();
                } else {
                    $('body').removeClass('expand-menu');
                    $('.subdrop + ul').slideUp();
                }
                return false;
            }
        });
    }

    // --- LOGIKA BARU UNTUK QR CODE DINAMIS ---
    // Cek apakah elemen QR Code ada di halaman ini
    if ($('#qr-code-container').length > 0) {
        
        const qrCodeContainer = document.getElementById('qr-code-container');
        const statusText = document.getElementById('status-text');
        let qrCode = null; // Variabel untuk menyimpan objek QR Code

        // Fungsi utama untuk meminta token dan membuat QR Code
        async function generateNewQRCode() {
            try {
                statusText.textContent = 'Membuat QR Code baru...';
                
                // Panggil API backend Anda untuk mendapatkan token baru
                const response = await fetch('api/generate_token.php');
                const data = await response.json();

                if (data.token) {
                    // Ganti URL ini dengan URL validasi Anda yang sebenarnya
                    const attendanceUrl = window.location.origin + '/validasi_absen.php?token=' + data.token;
                    
                    qrCodeContainer.innerHTML = ''; // Kosongkan kontainer

                    qrCode = new QRCode(qrCodeContainer, {
                        text: attendanceUrl,
                        width: 300,
                        height: 300,
                        colorDark: "#000000",
                        colorLight: "#ffffff",
                        correctLevel: QRCode.CorrectLevel.H
                    });

                    statusText.textContent = 'Silakan Scan QR Code.';
                } else {
                    statusText.textContent = 'Gagal mendapatkan token dari server.';
                }
            } catch (error) {
                console.error('Error fetching new token:', error);
                statusText.textContent = 'Koneksi ke server bermasalah.';
            }
        }

        // Fungsi untuk mendengarkan sinyal dari server
        function listenForScanSuccess() {
            const eventSource = new EventSource('api/listen_for_scan.php');

            eventSource.onmessage = function(event) {
                if (event.data === 'scan_success') {
                    console.log('Scan berhasil terdeteksi! Membuat QR Code baru.');
                    statusText.textContent = 'Absensi berhasil! Memuat QR Code berikutnya...';
                    generateNewQRCode();
                }
            };

            eventSource.onerror = function() {
                console.error('Koneksi Server-Sent Event gagal. Mencoba lagi dalam 5 detik...');
                eventSource.close();
                setTimeout(listenForScanSuccess, 5000);
            };
        }

        // Mulai siklusnya saat halaman QR Code dimuat
        generateNewQRCode();
        listenForScanSuccess();
    }
    // --- AKHIR DARI LOGIKA QR CODE ---

});
