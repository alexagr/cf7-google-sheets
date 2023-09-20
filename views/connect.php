<h1>Google Sheets for Contact Form 7</h1>
<hr>

<div class="wrap">
<h2>Step 1: create application credentials</h2>
<ul>
<li>* Go to <a href="https://console.developers.google.com">https://console.developers.google.com</a></li>
<li>* Choose existing project or create a new one.</li>
<li>* Click <b>Enable APIs And Services</b>.</li>
<li> &nbsp;&nbsp;* Search for <b>Google Sheet API</b> and enable it.</li>
<li>* Click <b>Credentials &gt; Create Credentials &gt; Service Account</b> - this will open <b>Create Service Account</b> screen.</li>
<li> &nbsp;&nbsp;* Under <b>Service account details:</b></li>
<li> &nbsp;&nbsp;&nbsp;&nbsp; * For <b>Service account name</b> select &quot;Google Sheets for Contact Form 7&quot;.</li>
<li> &nbsp;&nbsp;&nbsp;&nbsp;* Click <b>CREATE AND CONTINUE</b>.</li>
<li> &nbsp;&nbsp;* Under <b>Grant this service account access to the project:</b></li>
<li> &nbsp;&nbsp;&nbsp;&nbsp;* For <b>Role</b> enter &quot;Editor&quot;.</li>
<li> &nbsp;&nbsp;&nbsp;&nbsp;* Click <b>CONTINUE</b>.</li>
<li> &nbsp;&nbsp;* Click <b>DONE</b>.</b></li>
<li>* Click on the created service account under <b>Service Accounts</b> - this will open <b>Edit Service Account</b> screen.</li>
<li> &nbsp;&nbsp;* Switch to <b>KEYS</b> tab.</li>
<li> &nbsp;&nbsp;* Click <b>ADD KEY &gt; Create new key</b>.</li>
<li> &nbsp;&nbsp;&nbsp;&nbsp;* For <b>Key type</b> choose &quot;JSON&quot;.</li>
<li> &nbsp;&nbsp;&nbsp;&nbsp;* Click <b>CREATE</b> - credentials.json file will be downloaded.</li>
</ul>
</div>

<br>
<hr>

<div class="wrap">
<h2>Step 2: upload credentials.json file</h2>

<form enctype="multipart/form-data" method="POST" action="<?php echo esc_html(admin_url('admin-post.php')); ?>">
    <input type="hidden" name="action" value="cf7_sheets_action">
    <input type="hidden" name="action_name" value="upload_credentials">
    <?php wp_nonce_field('cf7_sheets_action', 'cf7_sheets'); ?>
    <input type="hidden" name="redirectToUrl" value="<?php echo cf7_sheets_view_pagename(); ?>">

    <div class="row">
      <input type="file" id="upload-credentials" name="file">
    </div>

    <?php cf7_sheets_submit(esc_html__('Upload')); ?>
</form>

<br>
<table class="form-table" role="presentation">
    <tr>
        <th scope="row"><label for="client_id">Client ID</label></th>
        <td><?php echo $client_id; ?></td>
    </tr>
    <tr>
        <th scope="row"><label for="client_email">Client email</label></th>
        <td><?php echo $client_email; ?></td>
    </tr>
</table>

</div>

<br>
<hr>

<div class="wrap">
<h2>Step 3: (optional) test connection to Google Sheets</h2>

<br>
<ul>
<li>* Go to <a href="https://docs.google.com/spreadsheets">https://docs.google.com/spreadsheets</a></li>
<li>* Open an existing sheet or create a new one.</li>
<li>* Click <b>Share</b> and grant <b>Editor</b> permissions to <b>Client email</b> as shown above.</li>
<li>* Determine <b>Sheet ID</b> from the Google Sheets URL, that looks as follows: https://docs.google.com/spreadsheets/d/<b>&lt;sheet-id&gt;</b>/edit</;li>
<li>* Enter <b>Sheet ID</b> and click <b>Test</b>.</;li>
</ul>

<form method="POST" action="<?php echo esc_html(admin_url('admin-post.php')); ?>" onkeydown="return event.key != 'Enter';">
    <input type="hidden" name="action" value="cf7_sheets_action">
    <input type="hidden" name="action_name" value="test_access">
    <?php wp_nonce_field('cf7_sheets_action', 'cf7_sheets'); ?>
    <input type="hidden" name="redirectToUrl" value="<?php echo cf7_sheets_view_pagename(); ?>">

    <table class="form-table" role="presentation">
        <tr>
            <th scope="row"><label for="sheets_id">Sheet ID</label></th>
            <td><?php echo $sheet_id; ?></td>
        </tr>
    </table>

    <?php cf7_sheets_submit(esc_html__('Test')); ?>
</form>
</div>

<?php
    if (cf7_sheets_log_exists()) {
        ?>
<br>
<hr>
<br>
<a href="<?php echo cf7_sheets_url('/log/log.txt'); ?>">View log</a>
        <?php
    }
  ?>
