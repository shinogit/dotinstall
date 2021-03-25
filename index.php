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
  $n = 10;
  $dbh->query("DELETE FROM posts WHERE likes < $n");
  // このように直接変数をクエリに書くのはだめ！
  // なぜなら今回の例でいくと$n = '10 OR 1=1'と値を入力された時に全てのデータが消えてしまうから
  $n = '10 OR 1=1';
  $dbh->query("DELETE FROM posts WHERE likes < $n");
  //このような攻撃をsqlインジェクションと呼ぶ
  


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
