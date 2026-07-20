<?php echo $header; ?><?php echo $column_left; ?>
<div id="content">
  <div class="page-header">
    <div class="container-fluid">
      <div class="pull-right">
        <button type="submit" form="form-payment" data-toggle="tooltip" title="<?php echo $button_save; ?>" class="btn btn-primary"><i class="fa fa-save"></i></button>
        <a href="<?php echo $cancel; ?>" data-toggle="tooltip" title="<?php echo $button_cancel; ?>" class="btn btn-default"><i class="fa fa-reply"></i></a>
      </div>
      <h1><?php echo $heading_title; ?></h1>
      <ul class="breadcrumb">
        <?php foreach ($breadcrumbs as $breadcrumb) { ?>
        <li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
        <?php } ?>
      </ul>
    </div>
  </div>
  <div class="container-fluid">
    <?php if ($error_warning) { ?>
    <div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo $error_warning; ?>
      <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
    <?php } ?>
    <div class="panel panel-default">
      <div class="panel-heading">
        <h3 class="panel-title"><i class="fa fa-pencil"></i> <?php echo $text_edit; ?></h3>
      </div>
      <div class="panel-body">
        <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form-payment" class="form-horizontal">
          
          <div class="form-group">
            <label class="col-sm-2 control-to-label" for="input-status"><?php echo $entry_status; ?></label>
            <div class="col-sm-10">
              <select name="komerce_status" id="input-status" class="form-control">
                <option value="1" <?php if ($komerce_status == '1') { echo 'selected="selected"'; } ?>><?php echo $text_enabled; ?></option>
                <option value="0" <?php if ($komerce_status == '0') { echo 'selected="selected"'; } ?>><?php echo $text_disabled; ?></option>
              </select>
            </div>
          </div>

          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-environment"><?php echo $entry_environment; ?></label>
            <div class="col-sm-10">
              <select name="komerce_environment" id="input-environment" class="form-control">
                <option value="sandbox" <?php if ($komerce_environment == 'sandbox') { echo 'selected="selected"'; } ?>><?php echo $text_sandbox; ?></option>
                <option value="production" <?php if ($komerce_environment == 'production') { echo 'selected="selected"'; } ?>><?php echo $text_production; ?></option>
              </select>
            </div>
          </div>

          <div class="form-group required">
            <label class="col-sm-2 control-label" for="input-apikey"><?php echo $entry_apikey; ?></label>
            <div class="col-sm-10">
              <input type="password" name="komerce_apikey" value="<?php echo $komerce_apikey; ?>" placeholder="Komerce OpenAPI API Key" id="input-apikey" class="form-control" />
              <?php if ($error_apikey) { ?>
              <div class="text-danger"><?php echo $error_apikey; ?></div>
              <?php } ?>
            </div>
          </div>

          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-client-id">Client ID</label>
            <div class="col-sm-10">
              <input type="text" name="komerce_client_id" value="<?php echo $komerce_client_id; ?>" placeholder="Client ID" id="input-client-id" class="form-control" />
            </div>
          </div>

          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-client-secret">Client Secret</label>
            <div class="col-sm-10">
              <input type="password" name="komerce_client_secret" value="<?php echo $komerce_client_secret; ?>" placeholder="Client Secret" id="input-client-secret" class="form-control" />
            </div>
          </div>

          <hr/>
          <h4><i class="fa fa-credit-card"></i> Payment Methods Activation</h4>
          
          <div class="form-group">
            <label class="col-sm-2 control-label">QRIS Payment</label>
            <div class="col-sm-10">
              <select name="komerce_qris_status" class="form-control">
                <option value="1" <?php if ($komerce_qris_status == '1') echo 'selected="selected"'; ?>>Enabled</option>
                <option value="0" <?php if ($komerce_qris_status == '0') echo 'selected="selected"'; ?>>Disabled</option>
              </select>
            </div>
          </div>

          <div class="form-group">
            <label class="col-sm-2 control-label">BCA Virtual Account</label>
            <div class="col-sm-10">
              <select name="komerce_bca_va_status" class="form-control">
                <option value="1" <?php if ($komerce_bca_va_status == '1') echo 'selected="selected"'; ?>>Enabled</option>
                <option value="0" <?php if ($komerce_bca_va_status == '0') echo 'selected="selected"'; ?>>Disabled</option>
              </select>
            </div>
          </div>

          <div class="form-group">
            <label class="col-sm-2 control-label">Mandiri Virtual Account</label>
            <div class="col-sm-10">
              <select name="komerce_mandiri_va_status" class="form-control">
                <option value="1" <?php if ($komerce_mandiri_va_status == '1') echo 'selected="selected"'; ?>>Enabled</option>
                <option value="0" <?php if ($komerce_mandiri_va_status == '0') echo 'selected="selected"'; ?>>Disabled</option>
              </select>
            </div>
          </div>

          <div class="form-group">
            <label class="col-sm-2 control-label">BNI Virtual Account</label>
            <div class="col-sm-10">
              <select name="komerce_bni_va_status" class="form-control">
                <option value="1" <?php if ($komerce_bni_va_status == '1') echo 'selected="selected"'; ?>>Enabled</option>
                <option value="0" <?php if ($komerce_bni_va_status == '0') echo 'selected="selected"'; ?>>Disabled</option>
              </select>
            </div>
          </div>

          <div class="form-group">
            <label class="col-sm-2 control-label">BRI Virtual Account</label>
            <div class="col-sm-10">
              <select name="komerce_bri_va_status" class="form-control">
                <option value="1" <?php if ($komerce_bri_va_status == '1') echo 'selected="selected"'; ?>>Enabled</option>
                <option value="0" <?php if ($komerce_bri_va_status == '0') echo 'selected="selected"'; ?>>Disabled</option>
              </select>
            </div>
          </div>

          <hr/>
          <h4><i class="fa fa-refresh"></i> Order Status Mapping (Webhook Sync)</h4>
          
          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-status-pending"><?php echo $entry_status_pending; ?></label>
            <div class="col-sm-10">
              <select name="komerce_status_pending_id" id="input-status-pending" class="form-control">
                <?php foreach ($order_statuses as $order_status) { ?>
                <option value="<?php echo $order_status['order_status_id']; ?>" <?php if ($order_status['order_status_id'] == $komerce_status_pending_id) { echo 'selected="selected"'; } ?>><?php echo $order_status['name']; ?></option>
                <?php } ?>
              </select>
            </div>
          </div>

          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-status-paid"><?php echo $entry_status_paid; ?></label>
            <div class="col-sm-10">
              <select name="komerce_status_paid_id" id="input-status-paid" class="form-control">
                <?php foreach ($order_statuses as $order_status) { ?>
                <option value="<?php echo $order_status['order_status_id']; ?>" <?php if ($order_status['order_status_id'] == $komerce_status_paid_id) { echo 'selected="selected"'; } ?>><?php echo $order_status['name']; ?></option>
                <?php } ?>
              </select>
            </div>
          </div>

          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-status-shipped"><?php echo $entry_status_shipped; ?></label>
            <div class="col-sm-10">
              <select name="komerce_status_shipped_id" id="input-status-shipped" class="form-control">
                <?php foreach ($order_statuses as $order_status) { ?>
                <option value="<?php echo $order_status['order_status_id']; ?>" <?php if ($order_status['order_status_id'] == $komerce_status_shipped_id) { echo 'selected="selected"'; } ?>><?php echo $order_status['name']; ?></option>
                <?php } ?>
              </select>
            </div>
          </div>

          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-status-cancelled"><?php echo $entry_status_cancelled; ?></label>
            <div class="col-sm-10">
              <select name="komerce_status_cancelled_id" id="input-status-cancelled" class="form-control">
                <?php foreach ($order_statuses as $order_status) { ?>
                <option value="<?php echo $order_status['order_status_id']; ?>" <?php if ($order_status['order_status_id'] == $komerce_status_cancelled_id) { echo 'selected="selected"'; } ?>><?php echo $order_status['name']; ?></option>
                <?php } ?>
              </select>
            </div>
          </div>

          <hr/>
          <h4><i class="fa fa-link"></i> Webhook Endpoint Setup</h4>
          
          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-webhook-token"><?php echo $entry_webhook_token; ?></label>
            <div class="col-sm-10">
              <input type="text" name="komerce_webhook_token" value="<?php echo $komerce_webhook_token; ?>" id="input-webhook-token" class="form-control" />
              <span class="help-block">Webhook Secret Token sent in headers as <code>X-Komerce-Signature</code> or validated in the webhook request to secure your callback endpoint.</span>
            </div>
          </div>

          <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo $entry_webhook_url; ?></label>
            <div class="col-sm-10">
              <input type="text" value="<?php echo $webhook_url; ?>" class="form-control" readonly />
              <span class="help-block">Salin URL di atas dan tempel di halaman Pengaturan Webhook pada Akun Mitra Komerce Anda.</span>
            </div>
          </div>

          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-sort-order"><?php echo $entry_sort_order; ?></label>
            <div class="col-sm-10">
              <input type="text" name="komerce_sort_order" value="<?php echo $komerce_sort_order; ?>" placeholder="<?php echo $entry_sort_order; ?>" id="input-sort-order" class="form-control" />
            </div>
          </div>

        </form>
      </div>
    </div>
  </div>
</div>
<?php echo $footer; ?>