<?php
$kind = array();

$kind[1] = '質問';
$kind[2] = 'ご意見';
$kind[3] = '資料請求';

$type = array();

$type[1] = 'フロントエンド';
$type[2] = 'バックエンド';
$type[3] = 'システム';

session_start();
$mode = 'input';
$errormessage = array();

if (isset($_POST['back']) && $_POST['back']) {

  // 何もしない

} else if (isset($_POST['confirm']) && $_POST['confirm']) {

  if (!$_POST['fullname']) {
    $errormessage[] = '名前を入力してください。';
  } else if (mb_strlen($_POST['fullname']) > 100) {
    $errormessage[] = '名前は100文字以内にしてください。';
  }

  $_SESSION['fullname'] = htmlspecialchars($_POST['fullname'], ENT_QUOTES);

  if (!$_POST['email']) {
    $errormessage[] = 'メールアドレスを入力してください。';
  } else if (mb_strlen($_POST['email']) > 200) {
    $errormessage[] = 'メールアドレスは200文字以内にしてください。';
  } else if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
    $errormessage[] = 'メールアドレスが不正です。';
  }

  $_SESSION['email'] = htmlspecialchars($_POST['email'], ENT_QUOTES);

  if (!$_POST['kind']) {
    $errormessage[] = '種別を入力してください。';
  } else if ($_POST['kind'] <= 0 || $_POST['kind'] >= 4) {
    $errormessage[] = '種別が不正です。';
  }

  $_SESSION['kind'] = htmlspecialchars($_POST['kind'], ENT_QUOTES);

  if (!isset($_POST['type']) || !$_POST['type']) {
    $errormessage[] = '職種を入力してください。';
  } else if ($_POST['type'] <= 0 || $_POST['type'] >= 4) {
    $errormessage[] = '職種が不正です。';
  }

  if (isset($_POST['type'])) {
    $_SESSION['type'] = htmlspecialchars($_POST['type'], ENT_QUOTES);
  }

  if (!$_POST['message']) {
    $errormessage[] = 'お問い合わせ内容を入力してください。';
  } else if (mb_strlen($_POST['message']) > 500) {
    $errormessage[] = 'お問い合わせ内容は500文字以内にしてください。';
  }

  $_SESSION['message'] = htmlspecialchars($_POST['message'], ENT_QUOTES);

  if ($errormessage) {
    $mode = 'input';
  } else {
    $token = bin2hex(random_bytes(32));
    $_SESSION['token'] = $token;

    $mode = 'confirm';
  }

} else if (isset($_POST['send']) && $_POST['send']) {

  if (!$_POST['token'] || !$_SESSION['token'] || !$_SESSION['email']) {

    $errormessage[] = '不正な処理が行われました。';
    $_SESSION = array();
    $mode = 'input';

  } else if ($_POST['token'] != $_SESSION['token']) {

    $errormessage[] = '不正な処理が行われました。';
    $_SESSION = array();
    $mode = 'input';

  } else {

    $message = "お問い合わせを受け付けました。\r\n"
              ."名前：".$_SESSION['fullname']."\r\n"
              ."メールアドレス：".$_SESSION['email']."\r\n"
              ."種別：".$kind[$_SESSION['kind']]."\r\n"
              ."職種：".$type[$_SESSION['type']]."\r\n"
              ."お問い合わせ内容：\r\n"
              .preg_replace("/\r\n|\r|\n/", "\r\n", $_SESSION['message']);

    mail($_SESSION['email'], 'お問い合わせありがとうございます。', $message);
    mail('kwjun519@gmail.com', 'お問い合わせありがとうございます。', $message);

    $_SESSION = array();
    $mode = 'send';

  }

} else {

  $_SESSION = array();

}
?>
<!DOCTYPE html>
<html lang="ja" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title>お問い合わせフォーム</title>
    <link rel="stylesheet" href="style.css">
  </head>
  <body>

    <h1>お問い合わせフォーム</h1>

    <?php if ($mode == 'input') { ?>
      <!-- 入力画面 -->
      <?php
      if ($errormessage) {
        echo '<div class="error-div" style="color: red;">';
        echo implode('<br>', $errormessage);
        echo '</div>';
      }
      ?>
      <form action="./index.php" method="post">
        <label>名前<span class="important">（必須）</span></label>
        <input type="text" name="fullname" value="<?php echo $_SESSION['fullname']; ?>"><br>
        <label>メールアドレス<span class="important">（必須）</span></label>
        <input type="email" name="email" value="<?php echo $_SESSION['email']; ?>"><br>
        <label>種別</label>
        <select name="kind">
        <?php foreach($kind as $key => $value){ ?>
          <?php if ($_SESSION['kind'] == $key){ ?>
            <option value="<?php echo $key; ?>" selected><?php echo $value; ?></option>
          <?php } else { ?>
            <option value="<?php echo $key; ?>"><?php echo $value; ?></option>
          <?php } ?>
        <?php } ?>
        </select><br>
        <label>職種</label><br>
        <?php foreach($type as $key => $value){ ?>
          <?php if ($_SESSION['type'] == $key) { ?>
            <input type="radio" name="type" value="<?php echo $key; ?>" checked><?php echo $value; ?>
          <?php } else { ?>
            <input type="radio" name="type" value="<?php echo $key; ?>"><?php echo $value; ?>
          <?php } ?>
        <?php } ?>
        <br>
        <label>お問い合わせ内容<span class="important">（必須）</span></label><br>
        <textarea name="message" rows="8" cols="40"><?php echo nl2br($_SESSION['message']); ?></textarea><br>
        <input type="submit" name="confirm" value="確認">
      </form>

    <?php } else if ($mode == 'confirm') { ?>
      <!-- 確認画面 -->
      <form action="./index.php" method="post">
        <input type="hidden" name="token" value="<?php echo $_SESSION['token']; ?>">
        <label>名前<span class="important">（必須）</span></label>
        <input type="text" name="fullname" value="<?php echo $_SESSION['fullname']; ?>"><br>
        <label>メールアドレス<span class="important">（必須）</span></label>
        <input type="email" name="email" value="<?php echo $_SESSION['email']; ?>"><br>
        <label>種別</label>
        <select>
          <option><?php echo $kind[$_SESSION['kind']]; ?></option>
        </select><br>
        <label>職種</label>
        <input type="text" value="<?php echo $type[$_SESSION['type']]; ?>"><br>
        <label>お問い合わせ内容<span class="important">（必須）</span></label><br>
        <textarea name="message" rows="8" cols="40"><?php echo nl2br($_SESSION['message']); ?></textarea><br>
        <input type="submit" name="back" value="戻る">
        <input type="submit" name="send" value="送信">
      </form>

    <?php } else { ?>
      <!-- 完了画面 -->
      <p>送信しました。お問い合わせありがとうございます。</p>
    <?php } ?>

  </body>
</html>
