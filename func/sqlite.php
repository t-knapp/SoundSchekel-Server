<?php

error_reporting(E_ALL);

function debug($data){
    echo '<pre>';
    print_r($data);
    echo '</pre>';
}

function line($data){
    echo $data . PHP_EOL;
}

/*
-- Describe SOUND
CREATE TABLE "sound" (
    "sid" INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    "seq" INTEGER NOT NULL,
    "key" TEXT NOT NULL,
    "title" TEXT NOT NULL,
    "length" TEXT NOT NULL
)
*/

/*
 * Singleton DB
 */ 
class DB {

  private static $instance;
    
  public static function getInstance(){
      if (!isset(self::$instance)) {
        self::$instance = new self();
    }
    return self::$instance;
  }
  
  public function getNextSeq(){
      $this->dbh->beginTransaction();
      
      $sql = 'UPDATE sequence SET seq_val = seq_val + 1 WHERE seq_name = "sync";';
      $this->dbh->exec($sql);

      $sql = 'SELECT seq_val FROM sequence;';
      $nextSeq = $this->dbh->query($sql)->fetchColumn();
      
      $this->dbh->commit();
      
      return $nextSeq;
  }
  
  public function getSeq(){
      $sql = "SELECT seq_val FROM sequence;";
      return $this->dbh->query($sql)->fetchColumn();
  }
  
  public function findAll(){
      $sql = 'SELECT * FROM sound;';
      $stmt = $this->dbh->query($sql);
      return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }
  
  public function getCategories(){
      $sql = 'SELECT DISTINCT key FROM sound;';
      $stmt = $this->dbh->query($sql);
      return $stmt->fetchAll(PDO::FETCH_COLUMN);
  }

  public function updateDuration($soundId, $duration){
      $seq = $this->getNextSeq();
      $sql = "UPDATE sound SET seq = :seq , length = :len WHERE sid = :sid;";
      $stmt = $this->dbh->prepare($sql);
      $stmt->bindParam(":sid", $soundId,  PDO::PARAM_INT);
      $stmt->bindParam(":seq", $seq,      PDO::PARAM_INT);
      $stmt->bindParam(":len", $duration, PDO::PARAM_STR);
      $stmt->execute();
  }

  public function findBySoundId($soundId){
      $sql = "SELECT * FROM sound WHERE sid = :sid;";
      $stmt = $this->dbh->prepare($sql);
      $stmt->bindParam(":sid", $soundId, PDO::PARAM_INT);
      $stmt->execute();
      return $stmt->fetch(PDO::FETCH_ASSOC);
  }
  
  public function findBySeqGreaterThan($seq){
      $sql = "SELECT * FROM sound WHERE seq > {$seq};";
      return $this->dbh->query($sql)->fetchAll(PDO::FETCH_ASSOC);
  }
  
  public function delete($soundId){
      $sql = 'DELETE FROM sequence WHERE sid = :sid';
      $stmt = $this->dbh->prepare($sql);
      $stmt->bindParam(":sid", $soundId, PDO::PARAM_INT);
      $stmt->execute();
  }
  
  public function truncateFiles(){
      $this->deleteTableContent("sound");
  }
  
  private function deleteTableContent($table){
      $sql = "DELETE FROM '{$table}';";
      $stmt = $this->dbh->prepare($sql);
      $stmt->execute();
  }
  
  public function printSounds(){
      debug($this->findAll());
  }
  
  public function errors(){
      print_r($this->dbh->errorInfo());
  }

  /*
   * Adds a sound record to DB
   */
  public function insertSound($seq, $key, $title, $length){
      $sql = 'INSERT into sound(seq, key, title, length)
              VALUES(:seq, :key, :title, :length)';
      $stmt = $this->dbh->prepare($sql);
      
      $stmt->bindParam(":seq",    $seq,    PDO::PARAM_INT);
      $stmt->bindParam(":key",    $key,    PDO::PARAM_STR);
      $stmt->bindParam(":title",  $title,  PDO::PARAM_STR);
      $stmt->bindParam(":length", $length, PDO::PARAM_STR);
      
      $stmt->execute();
      #print_r($this->dbh->errorInfo());
      
      return $this->dbh->lastInsertId();
  }
  
  /*
   * Prints all meta information about all tables in <pre></pre> tags
   */
  public function describeTables(){
      $sql = 'SELECT * FROM sqlite_master';
      $result = $this->dbh->query($sql);
      echo "<pre>";
      print_r($result->fetchAll());
      echo "</pre>";
  }

  private $dsn = "sqlite:db/db.sqlite3";
  private $dbh;
  
  private function __construct(){
    try {
      $this->dbh = new PDO($this->dsn);
    } catch (PDOException $e) {
      echo 'Connection failed: ' . $e->getMessage();
    }
  }
  
}
