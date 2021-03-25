<?php

$dsn = 'mysql:host=ip-10-0-10-57.ap-northeast-1.compute.internal;dbname=test;charaset=utf8';
$user = 'nagisa';
$password = '';

try{
  $dbh = new PDO(
    $dsn,
    $user,
    $password,
    [
      // エラーが出た時に PDOException クラス形式で例外を投げてくれるので、 try-catch で例外を補足する。
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
      // PDOだとデフォルトでエミュレートモードという設定がオンになっているためINTで作成したカラムもSTRING型になってしまう。=>falseでオフにする。
      PDO::ATTR_EMULATE_PREPARES => false
    ]
  );
  // postsテーブルが存在していたらDrop（テーブルをまるっと削除）
  $dbh->query("DROP TABLE IF EXISTS posts");

  $dbh->query(
    "CREATE TABLE posts (
      id INT NOT NULL AUTO_INCREMENT,
      message VARCHAR (140),
      likes INT,
      PRIMARY KEY (id)
    )"
  );
  $dbh->query(
    "INSERT INTO posts (message, likes) VALUES
      ('Thanks', 12),
      ('thanks', 4),
      ('Arogato', 15)"
  );

  // likesが10より小さいものを削除
  // SQLインジェクションを想定して下記の値を入力されたとする
  $n = '10 OR 1=1';
  // プリペアドステートメントを使うことでSQLインジェクションを対策できる
  // PDO ステートメントオブジェクトが返ってくるので、分かりやすい変数名で受ける$stmt
  $stmt = $dbh->prepare("DELETE FROM posts WHERE likes < ?");
  //　　うするかというと、 execute() メソッドを PDO ステートメントオブジェクトに対して使い、引数にプレースホルダと紐づける値を配列で渡す。
  $stmt->execute([$n]);
  // プリペアドステートメントをつかわないと　DELETE FROM posts WHERE likes < 10 OR 1=1　となって全て削除されてしまうが、今回は使用しているので文字列に変換され下記のようになる
  // DELETE FROM posts WHERE likes < '10 OR 1=1'
  // また、 SQL では整数型である likes の条件として文字列を指定した場合、数字として解釈できるところまではそれを使って、それ以降は無視するので、結果としてこちらのクエリは SQL によって下記のように解釈される。
  // DELETE FROM posts WHERE likes < 10


  $stmt = $dbh->query("SELECT * FROM posts");
  $posts = $stmt->fetchAll();
  foreach($posts as $post) {
    printf(
      '%s (%d)' .  PHP_EOL,
      $post['message'],
      $post['likes']
    );
  }

  // catch する例外は PDOException の形式なので、このように書いて、エラーの情報が入ったオブジェクトを e で受け取ることができる。
} catch (PDOException $e) {
  echo $e->getMessage() . PHP_EOL;
  exit;
}
