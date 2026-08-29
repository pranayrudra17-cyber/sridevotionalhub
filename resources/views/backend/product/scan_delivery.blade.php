@extends('backend.layouts.app')

@section('content')
<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class="row align-items-center">
        <div class="col">
            <h1 class="h3">{{ translate('Scan Product QR') }}</h1>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{ translate('QR scanner / camera') }}</h5>
            </div>
            <div class="card-body">
                <div id="qr-reader-wrap" class="position-relative bg-dark rounded overflow-hidden" style="min-height: 280px;">
                    <div id="qr-reader" style="width: 100%;"></div>
                    <div id="qr-placeholder" class="d-flex align-items-center justify-content-center text-white-50" style="min-height: 280px;">
                        {{ translate('Camera preview will appear here') }}
                    </div>
                    <div id="qr-loading" class="d-none position-absolute w-100 h-100" style="top:0;left:0;background:rgba(0,0,0,.55);z-index:5;">
                        <div class="d-flex flex-column align-items-center justify-content-center h-100 text-white">
                            <div class="spinner-border mb-2" role="status"></div>
                            <span>{{ translate('Processing scanned QR code...') }}</span>
                        </div>
                    </div>
                </div>
                <div class="mt-3 d-flex flex-wrap" style="gap: 8px;">
                    <button type="button" id="start-scanner" class="btn btn-primary">{{ translate('Start Scanner') }}</button>
                    <button type="button" id="stop-scanner" class="btn btn-secondary" disabled>{{ translate('Stop Scanner') }}</button>
                </div>
                <p class="text-muted fs-12 mt-2 mb-0">{{ translate('Allow camera access when prompted. Works with a desktop webcam or a mobile camera.') }}</p>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{ translate('Scan result / status') }}</h5>
            </div>
            <div class="card-body">
                <div id="scan-alert" class="alert d-none" role="alert"></div>
                <div id="scan-info" class="d-none">
                    <p class="mb-1"><strong>{{ translate('Product') }}:</strong> <span id="info-product">-</span></p>
                    <p class="mb-1"><strong>{{ translate('Order') }}:</strong> <span id="info-order">-</span></p>
                    <p class="mb-1"><strong>{{ translate('Current Status') }}:</strong> <span id="info-current-status">-</span></p>
                    <p class="mb-1"><strong>{{ translate('New Status') }}:</strong> <span id="info-new-status">-</span></p>
                    <p class="mb-0"><strong>{{ translate('Delivered At') }}:</strong> <span id="info-delivered-at">-</span></p>
                </div>
                <div id="scan-empty" class="text-muted">{{ translate('Scan a product QR code to update delivery status.') }}</div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script type="text/javascript">
    (function () {
        var scanner = null;
        var scanning = false;
        var processing = false;
        var scanUrl = '{{ route('admin.product.scan.delivery') }}';
        var csrfToken = $('meta[name="csrf-token"]').attr('content');

        function setLoading(on) {
            $('#qr-loading').toggleClass('d-none', !on);
        }

        function showAlert(type, message) {
            var $alert = $('#scan-alert');
            $alert.removeClass('d-none alert-success alert-danger alert-warning alert-info')
                .addClass('alert-' + type)
                .text(message);
            AIZ.plugins.notify(type === 'danger' ? 'danger' : (type === 'success' ? 'success' : 'info'), message);
        }

        function fillInfo(data) {
            if (!data) {
                return;
            }
            $('#info-product').text(data.product || '-');
            $('#info-order').text(data.order || '-');
            $('#info-current-status').text(data.current_status || '-');
            $('#info-new-status').text(data.new_status || '-');
            $('#info-delivered-at').text(data.delivered_at || '-');
            $('#scan-info').removeClass('d-none');
            $('#scan-empty').addClass('d-none');
        }

        function setButtons(isScanning) {
            $('#start-scanner').prop('disabled', isScanning);
            $('#stop-scanner').prop('disabled', !isScanning);
            $('#qr-placeholder').toggleClass('d-none', isScanning);
        }

        function stopScanner() {
            var deferred = $.Deferred();
            if (!scanner || !scanning) {
                scanning = false;
                setButtons(false);
                deferred.resolve();
                return deferred.promise();
            }
            scanner.stop().then(function () {
                scanning = false;
                setButtons(false);
                deferred.resolve();
            }).catch(function () {
                scanning = false;
                setButtons(false);
                deferred.resolve();
            });
            return deferred.promise();
        }

        function processQr(decodedText) {
            if (processing) {
                return;
            }
            processing = true;
            setLoading(true);

            stopScanner().always(function () {
                $.ajax({
                    url: scanUrl,
                    type: 'POST',
                    dataType: 'json',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    data: {
                        _token: csrfToken,
                        qr_code: decodedText
                    }
                }).done(function (res) {
                    if (res && res.success) {
                        showAlert('success', res.message);
                    } else {
                        showAlert('danger', (res && res.message) ? res.message : '{{ translate('Unable to update delivery status.') }}');
                    }
                    if (res && res.data) {
                        fillInfo(res.data);
                    }
                }).fail(function (xhr) {
                    if (xhr.status === 401) {
                        showAlert('danger', '{{ translate('Please log in to continue.') }}');
                        window.location.href = '{{ route('login') }}';
                        return;
                    }
                    if (xhr.status === 403) {
                        showAlert('danger', '{{ translate('You are not authorized to scan product QR codes.') }}');
                        return;
                    }
                    var res = xhr.responseJSON || {};
                    showAlert('danger', res.message || '{{ translate('Unable to update delivery status.') }}');
                    if (res.data) {
                        fillInfo(res.data);
                    }
                }).always(function () {
                    processing = false;
                    setLoading(false);
                });
            });
        }

        function startScanner() {
            if (scanning || processing) {
                return;
            }
            if (!window.Html5Qrcode) {
                showAlert('danger', '{{ translate('QR scanner library failed to load.') }}');
                return;
            }
            if (!window.isSecureContext && location.hostname !== 'localhost' && location.hostname !== '127.0.0.1') {
                showAlert('warning', '{{ translate('Camera access requires HTTPS or localhost.') }}');
            }

            scanner = scanner || new Html5Qrcode('qr-reader');
            var config = {
                fps: 10,
                qrbox: function (viewfinderWidth, viewfinderHeight) {
                    var minEdge = Math.min(viewfinderWidth, viewfinderHeight);
                    var size = Math.max(180, Math.floor(minEdge * 0.7));
                    return { width: size, height: size };
                },
                aspectRatio: 1.0
            };

            scanner.start(
                { facingMode: 'environment' },
                config,
                function (decodedText) {
                    if (!decodedText || processing) {
                        return;
                    }
                    processQr(decodedText);
                }
            ).then(function () {
                scanning = true;
                setButtons(true);
            }).catch(function (err) {
                var message = '{{ translate('Camera is unavailable.') }}';
                var errText = (err && err.toString) ? err.toString() : '';
                if (errText.indexOf('NotAllowedError') !== -1 || errText.indexOf('Permission') !== -1) {
                    message = '{{ translate('Camera permission denied. Please allow camera access and try again.') }}';
                } else if (errText.indexOf('NotFoundError') !== -1) {
                    message = '{{ translate('No camera was found on this device.') }}';
                }
                showAlert('danger', message);
                scanning = false;
                setButtons(false);
            });
        }

        $('#start-scanner').on('click', startScanner);
        $('#stop-scanner').on('click', function () {
            stopScanner();
        });

        $(window).on('beforeunload', function () {
            if (scanner && scanning) {
                scanner.stop().catch(function () {});
            }
        });
    })();
</script>
@endsection
