<?php echo $header; ?><?php echo $column_left; ?>
<div id="content">
  <div class="page-header">
    <div class="container-fluid">
      <div class="pull-right">
        <button type="submit" form="form-payment" data-toggle="tooltip" title="Save" class="btn btn-primary"><i class="fa fa-save"></i></button>
        <a href="<?php echo $cancel; ?>" data-toggle="tooltip" title="Cancel" class="btn btn-default"><i class="fa fa-reply"></i></a>
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
          
          <?php foreach ($languages as $language) { ?>
          <div class="form-group required">
            <label class="col-sm-2 control-label" for="input-bank<?php echo $language['language_id']; ?>">Instruksi Pembayaran (<?php echo $language['name']; ?>)</label>
            <div class="col-sm-10">
              <textarea name="rajaongkir_pay_bank_<?php echo $language['language_id']; ?>" rows="5" placeholder="Instruksi transfer bank" id="input-bank<?php echo $language['language_id']; ?>" class="form-control"><?php echo isset(${'rajaongkir_pay_bank_' . $language['language_id']}) ? ${'rajaongkir_pay_bank_' . $language['language_id']} : ''; ?></textarea>
            </div>
          </div>
          <?php } ?>

          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-status">Status</label>
            <div class="col-sm-10">
              <select name="rajaongkir_pay_status" id="input-status" class="form-control">
                <option value="1" <?php if ($rajaongkir_pay_status == '1') echo 'selected="selected"'; ?>>Enabled</option>
                <option value="0" <?php if ($rajaongkir_pay_status == '0') echo 'selected="selected"'; ?>>Disabled</option>
              </select>
            </div>
          </div>

          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-sort-order">Sort Order</label>
            <div class="col-sm-10">
              <input type="text" name="rajaongkir_pay_sort_order" value="<?php echo $rajaongkir_pay_sort_order; ?>" id="input-sort-order" class="form-control" />
            </div>
          </div>

        </form>
      </div>
    </div>
  </div>
</div>
<?php echo $footer; ?>