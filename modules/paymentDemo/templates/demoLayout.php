<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
  <head>
    <?php include_http_metas() ?>
    <?php include_metas() ?>
    <?php include_title() ?>
    <link rel="shortcut icon" href="/favicon.ico" />
    <?php include_stylesheets() ?>
    <?php include_javascripts() ?>
    <style type="text/css">
      body {
        margin: 0px;
        padding-left: 2em;
      }      
      thead {
        background-color: #ddd; 
      }
      .error_list {
        color: #ff0000;
      }
    </style>
    <script language="javascript" type="text/javascript">
      $(function() {
        $('.error_list').next('input').css('border', '1px solid red');
      });
    </script>
  </head>
  <body>
    <h1>jmsPaymentPlugin Demo</h1>
    <div id="content"> 
      <?php echo $sf_content ?>
    </div>
  </body>
</html>
