@extends('backend.layout.main')

@section('content')
<div class="page-header">
    <div class="page-block">
        <div class="row align-items-center">
            <div class="col-12">
                <h3 class="page-title">Delivery Notification Management</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="ti ti-home"></i> Dashboard</a></li>
                    <li class="breadcrumb-item active">Delivery Notifications</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="page-block">
    <div class="row">
        <!-- SMS Templates Section -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>SMS Templates</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label>Order Placed</label>
                        <textarea class="form-control" rows="2" id="sms_order_placed">Your order {{order_id}} has been placed successfully. We will notify you when it is assigned to a delivery man.</textarea>
                    </div>
                    <div class="mb-3">
                        <label>Order Out for Delivery</label>
                        <textarea class="form-control" rows="2" id="sms_out_for_delivery">Your order {{order_id}} is out for delivery. Delivery man {{delivery_man_name}} is on the way.</textarea>
                    </div>
                    <div class="mb-3">
                        <label>Order Delivered</label>
                        <textarea class="form-control" rows="2" id="sms_delivered">Your order {{order_id}} has been delivered successfully. Please rate your delivery experience.</textarea>
                    </div>
                    <div class="mb-3">
                        <label>Payment Received</label>
                        <textarea class="form-control" rows="2" id="sms_payment_received">Payment received for order {{order_id}} of amount {{amount}}. Thank you!</textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- Push Notification Templates Section -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>Push Notification Templates</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label>New Order Assigned</label>
                        <textarea class="form-control" rows="2" id="push_new_order">New order {{order_id}} assigned to you.</textarea>
                    </div>
                    <div class="mb-3">
                        <label>Route Change</label>
                        <textarea class="form-control" rows="2" id="push_route_change">Route updated for order {{order_id}}.</textarea>
                    </div>
                    <div class="mb-3">
                        <label>Payment Reminder</label>
                        <textarea class="form-control" rows="2" id="push_payment_reminder">Payment reminder for order {{order_id}} of amount {{amount}}.</textarea>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Notification Settings -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5>Notification Settings</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>SMS Service Provider</label>
                                <select class="form-control">
                                    <option>Twilio</option>
                                    <option>AWS SNS</option>
                                    <option>ClickSend</option>
                                    <option>Vonage</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Push Notification Service</label>
                                <select class="form-control">
                                    <option>Firebase Cloud Messaging</option>
                                    <option>OneSignal</option>
                                    <option>Expo</option>
                                    <option>SignalWire</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Email Service</label>
                                <select class="form-control">
                                    <option>SendGrid</option>
                                    <option>Mailgun</option>
                                    <option>Amazon SES</option>
                                    <option>SMTP</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-6">
                            <h6>SMS Configuration</h6>
                            <div class="form-group">
                                <label>Sender ID</label>
                                <input type="text" class="form-control" placeholder="e.g., DELIVER">
                            </div>
                            <div class="form-group">
                                <label>Character Limit</label>
                                <input type="number" class="form-control" value="160">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6>Push Configuration</h6>
                            <div class="form-group">
                                <label>Priority Level</label>
                                <select class="form-control">
                                    <option>High</option>
                                    <option>Normal</option>
                                    <option>Low</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Notification Sound</label>
                                <select class="form-control">
                                    <option>default</option>
                                    <option>alarm</option>
                                    <option>notification</option>
                                    <option>silent</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <button class="btn btn-primary">Save Notification Settings</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Manual Notification Testing -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5>Manual Notification Testing</h5>
                </div>
                <div class="card-body">
                    <form>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Delivery Man</label>
                                    <select class="form-control">
                                        <option value="1">John Doe (Motorcycle)</option>
                                        <option value="2">Jane Smith (Car)</option>
                                        <option value="3">Mike Johnson (Van)</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Notification Type</label>
                                    <select class="form-control">
                                        <option>SMS</option>
                                        <option>Push</option>
                                        <option>Email</option>
                                        <option>All</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Test Message</label>
                                    <textarea class="form-control" rows="2" placeholder="Enter test message...">This is a test notification.</textarea>
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-success">Send Test Notification</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection