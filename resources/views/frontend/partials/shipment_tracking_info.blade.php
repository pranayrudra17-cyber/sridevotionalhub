@php $awb = ((!empty($trackingDetails)) && ($trackingDetails['shipment_status'] != 0))?$trackingDetails['shipment_track'][0]['awb_code']:null; @endphp
                @if($awb != null)
                    <div class="card rounded-0 border mt-2 mb-0">
                        <div class="card-header border-bottom-0">
                            <h5 class="fs-16 fw-700 text-dark mb-0">{{ translate('Tracking Info') }}</h5>
                            <h6 class="text-right fs-14">{{ translate('Tracking ID') }}: <span class="text-primary">{{ $awb }}</span></h6>
                        </div>
                        <div class="card-body pt-0">
                            @if($trackingDetails['track_status'] == 1)
                                <div class="pt-2 pb-3 fw-200 font-manrope fs-16">
                                    @if($trackingDetails['shipment_track'][0]['current_status'] == 'Delivered')
                                        {{ translate('Delivered On') }} <span class="font-bold fw-800 fs-20 delivered-date"> {{ date('d M, Y', strtotime($trackingDetails['shipment_track'][0]['delivered_date'])) }}. </span>
                                    @else
                                        {{ translate('The delivery is estimated on or before') }} <span class="font-bold fw-800 delivered-date"> {{ add_some_days(strtotime($trackingDetails['etd'])) }} </span> {{ translate('for this order.') }}
                                    @endif
                                    <hr>
                                </div>
                            @endif
                            @if($trackingDetails['shipment_track_activities'] != null)
                                <div class="row">
                                    <div class="col-md-12 col-lg-12">
                                    <div id="tracking-pre"></div>
                                    <div id="tracking">
                                        <div class="text-center tracking-status-intransit {{ ($trackingDetails['shipment_track'][0]['current_status'] == 'Delivered')?'bg-success':'bg-primary' }}">
                                            <p class="tracking-status text-tight"> @if($trackingDetails['shipment_track'][0]['current_status'] == 'Delivered') <i class="la la-check-circle-o pr-2"></i> @endif {{ translate($trackingDetails['shipment_track'][0]['current_status']) }}</p>
                                        </div>
                                        <div class="tracking-list">
                                            @foreach($trackingDetails['shipment_track_activities'] as $activities)
                                                <div class="tracking-item">
                                                    <div class="tracking-icon {{ (($activities['sr-status-label'] == 'OUT FOR DELIVERY')?'status-outfordelivery':'status-intransit') }}">
                                                    @if($activities['sr-status-label'] == 'OUT FOR DELIVERY')
                                                        <svg class="svg-inline--fa fa-shipping-fast fa-w-20" aria-hidden="true" data-prefix="fas" data-icon="shipping-fast" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512" data-fa-i2svg="">
                                                            <path fill="currentColor" d="M624 352h-16V243.9c0-12.7-5.1-24.9-14.1-33.9L494 110.1c-9-9-21.2-14.1-33.9-14.1H416V48c0-26.5-21.5-48-48-48H112C85.5 0 64 21.5 64 48v48H8c-4.4 0-8 3.6-8 8v16c0 4.4 3.6 8 8 8h272c4.4 0 8 3.6 8 8v16c0 4.4-3.6 8-8 8H40c-4.4 0-8 3.6-8 8v16c0 4.4 3.6 8 8 8h208c4.4 0 8 3.6 8 8v16c0 4.4-3.6 8-8 8H8c-4.4 0-8 3.6-8 8v16c0 4.4 3.6 8 8 8h208c4.4 0 8 3.6 8 8v16c0 4.4-3.6 8-8 8H64v128c0 53 43 96 96 96s96-43 96-96h128c0 53 43 96 96 96s96-43 96-96h48c8.8 0 16-7.2 16-16v-32c0-8.8-7.2-16-16-16zM160 464c-26.5 0-48-21.5-48-48s21.5-48 48-48 48 21.5 48 48-21.5 48-48 48zm320 0c-26.5 0-48-21.5-48-48s21.5-48 48-48 48 21.5 48 48-21.5 48-48 48zm80-208H416V144h44.1l99.9 99.9V256z"></path>
                                                        </svg>
                                                        <!-- <i class="fas fa-shipping-fast"></i> -->
                                                    @else
                                                        <svg class="svg-inline--fa fa-circle fa-w-16" aria-hidden="true" data-prefix="fas" data-icon="circle" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" data-fa-i2svg="">
                                                            <path fill="currentColor" d="M256 8C119 8 8 119 8 256s111 248 248 248 248-111 248-248S393 8 256 8z"></path>
                                                        </svg>
                                                        <!-- <i class="fas fa-circle"></i> -->
                                                    @endif
                                                    </div>
                                                    <div class="tracking-date">{{ date('M d, Y', strtotime($activities['date'])) }}<span>{{ date('h:i A', strtotime($activities['date'])) }}</span></div>
                                                    <div class="tracking-content">{{ $activities['activity'] }}<span>{{ $activities['location'] }}</span></div>
                                                </div>
                                            @endforeach
                                            <div class="tracking-item">
                                                <div class="tracking-icon status-inforeceived">
                                                <svg class="svg-inline--fa fa-clipboard-list fa-w-12" aria-hidden="true" data-prefix="fas" data-icon="clipboard-list" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512" data-fa-i2svg="">
                                                    <path fill="currentColor" d="M336 64h-80c0-35.3-28.7-64-64-64s-64 28.7-64 64H48C21.5 64 0 85.5 0 112v352c0 26.5 21.5 48 48 48h288c26.5 0 48-21.5 48-48V112c0-26.5-21.5-48-48-48zM96 424c-13.3 0-24-10.7-24-24s10.7-24 24-24 24 10.7 24 24-10.7 24-24 24zm0-96c-13.3 0-24-10.7-24-24s10.7-24 24-24 24 10.7 24 24-10.7 24-24 24zm0-96c-13.3 0-24-10.7-24-24s10.7-24 24-24 24 10.7 24 24-10.7 24-24 24zm96-192c13.3 0 24 10.7 24 24s-10.7 24-24 24-24-10.7-24-24 10.7-24 24-24zm128 368c0 4.4-3.6 8-8 8H168c-4.4 0-8-3.6-8-8v-16c0-4.4 3.6-8 8-8h144c4.4 0 8 3.6 8 8v16zm0-96c0 4.4-3.6 8-8 8H168c-4.4 0-8-3.6-8-8v-16c0-4.4 3.6-8 8-8h144c4.4 0 8 3.6 8 8v16zm0-96c0 4.4-3.6 8-8 8H168c-4.4 0-8-3.6-8-8v-16c0-4.4 3.6-8 8-8h144c4.4 0 8 3.6 8 8v16z"></path>
                                                </svg>
                                                <!-- <i class="fas fa-clipboard-list"></i> -->
                                                </div>
                                                <div class="tracking-date">{{ date('M d, Y', strtotime($order->created_at)) }}<span>{{ date('h:i A', strtotime($order->created_at)) }}</span></div>
                                                <div class="tracking-content">{{ translate('Order Received') }}<span>{{ json_decode($order->shipping_address)->address }}, {{ json_decode($order->shipping_address)->city }}.</span></div>
                                            </div>
                                        </div>
                                    </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                {{-- @elseif($trackingDetails['status'] == 'CANCELED')
                    <div class="card rounded-0 border mt-2 mb-0">
                        <div class="card-header border-bottom-0">
                            <h5 class="fs-16 fw-700 text-dark mb-0">{{ translate('Tracking Info') }}</h5>
                        </div>
                        <div class="card-body pt-0">
                            <div class="row">
                                <div class="col-md-12 col-lg-12">
                                    <div id="tracking-pre"></div>
                                    <div id="tracking" style="margin-bottom: 8px;">
                                        <div class="text-center tracking-status-intransit bg-danger p-3">
                                            <p class="tracking-status text-tight">{{ translate($shipmentDetails['status']) }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div> --}}
                @endif