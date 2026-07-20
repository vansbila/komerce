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
          
          <!-- Status Aktif/Nonaktif -->
          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-status"><?php echo $entry_status; ?></label>
            <div class="col-sm-10">
              <select name="komerce_status" id="input-status" class="form-control">
                <option value="1" <?php if ($komerce_status == '1') { echo 'selected="selected"'; } ?>><?php echo $text_enabled; ?></option>
                <option value="0" <?php if ($komerce_status == '0') { echo 'selected="selected"'; } ?>><?php echo $text_disabled; ?></option>
              </select>
            </div>
          </div>

          <!-- Tipe Akun RajaOngkir (Penting untuk menentukan Endpoint URL) -->
          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-account-type"><?php echo $entry_account_type; ?></label>
            <div class="col-sm-10">
              <select name="komerce_account_type" id="input-account-type" class="form-control">
                <option value="starter" <?php if ($komerce_account_type == 'starter') { echo 'selected="selected"'; } ?>>Starter</option>
                <option value="basic" <?php if ($komerce_account_type == 'basic') { echo 'selected="selected"'; } ?>>Basic</option>
                <option value="pro" <?php if ($komerce_account_type == 'pro') { echo 'selected="selected"'; } ?>>Pro</option>
              </select>
              <span class="help-block">Sesuaikan dengan tipe akun yang Anda miliki di RajaOngkir.</span>
            </div>
          </div>

          <!-- API Key RajaOngkir -->
          <div class="form-group required">
            <label class="col-sm-2 control-label" for="input-apikey"><?php echo $entry_apikey; ?></label>
            <div class="col-sm-10">
              <input type="password" name="komerce_apikey" value="<?php echo $komerce_apikey; ?>" placeholder="RajaOngkir API Key" id="input-apikey" class="form-control" />
              <?php if ($error_apikey) { ?>
              <div class="text-danger"><?php echo $error_apikey; ?></div>
              <?php } ?>
            </div>
          </div>

          <hr/>
          <h4><i class="fa fa-refresh"></i> Order Status Mapping</h4>
          
          <!-- Status saat pesanan baru dibuat -->
          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-order-status"><?php echo $entry_order_status; ?></label>
            <div class="col-sm-10">
              <select name="komerce_order_status_id" id="input-order-status" class="form-control">
                <?php foreach ($order_statuses as $order_status) { ?>
                <option value="<?php echo $order_status['order_status_id']; ?>" <?php if ($order_status['order_status_id'] == $komerce_order_status_id) { echo 'selected="selected"'; } ?>><?php echo $order_status['name']; ?></option>
                <?php } ?>
              </select>
              <span class="help-block">Status awal saat pelanggan dialihkan ke halaman pembayaran.</span>
            </div>
          </div>

          <!-- Status saat pembayaran Lunas (Settlement) -->
          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-order-success-status"><?php echo $entry_order_success_status; ?></label>
            <div class="col-sm-10">
              <select name="komerce_order_success_status_id" id="input-order-success-status" class="form-control">
                <?php foreach ($order_statuses as $order_status) { ?>
                <option value="<?php echo $order_status['order_status_id']; ?>" <?php if ($order_status['order_status_id'] == $komerce_order_success_status_id) { echo 'selected="selected"'; } ?>><?php echo $order_status['name']; ?></option>
                <?php } ?>
              </select>
              <span class="help-block">Status otomatis jika RajaOngkir mengirim notifikasi pembayaran sukses.</span>
            </div>
          </div>

          <!-- Status saat Gagal/Kadaluarsa -->
          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-order-failed-status"><?php echo $entry_order_failed_status; ?></label>
            <div class="col-sm-10">
              <select name="komerce_order_failed_status_id" id="input-order-failed-status" class="form-control">
                <?php foreach ($order_statuses as $order_status) { ?>
                <option value="<?php echo $order_status['order_status_id']; ?>" <?php if ($order_status['order_status_id'] == $komerce_order_failed_status_id) { echo 'selected="selected"'; } ?>><?php echo $order_status['name']; ?></option>
                <?php } ?>
              </select>
            </div>
          </div>

          <hr/>
          <h4><i class="fa fa-link"></i> Callback Setup</h4>
          
          <div class="form-group">
            <label class="col-sm-2 control-label">Callback URL</label>
            <div class="col-sm-10">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-globe"></i></span>
                <input type="text" value="<?php echo $webhook_url; ?>" class="form-control" readonly />
              </div>
              <span class="help-block">Salin URL ini ke <strong>Dashboard RajaOngkir > Pengaturan > Callback URL</strong> agar status pesanan terupdate otomatis.</span>
            </div>
          </div>

          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-sort-order"><?php echo $entry_sort_order; ?></label>
            <div class="col-sm-10">
              <input type="text" name="komerce_sort_order" value="<?php echo $komerce_sort_order; ?>" placeholder="0" id="input-sort-order" class="form-control" />
            </div>
          </div>

        </form>
      </div>
    </div>
  </div>
</div>
<?php echo $footer; ?>
