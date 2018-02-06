<?php
class database extends PDO {
  public function __construct ($steffi=false) {
    if ($steffi)
      parent::__construct ("mysql:host=localhost;dbname=site_j2site;charset=utf8", "j2_steffi", "ffddff");
    else
      parent::__construct ("mysql:host=localhost;dbname=site_j2site;charset=utf8", "dbu-j2site", "Kks7$3q9");
  }
  
  //log
  public function log($msg) {
    $stmt = $this->prepare("INSERT INTO logs (msg, dt) VALUES (:msg, NOW())");
    $stmt->execute(array(":msg"=>$msg));
  }
  
  
  
  //auth
  public function login ($user, $pw) {
    $stmt = $this->prepare("SELECT * FROM user WHERE name=:name AND (pwenc=SHA2(:pw, 256) OR (pworig=:pw AND pwenc=''))");
    $stmt->execute(array(":name" => $user, ":pw" => $pw));
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!empty($result)) {
      //loggedin as student
      $result['student'] = true;
      //write login-table
      $stmt = $this->prepare("INSERT INTO logins (userid, dt) VALUES (:userid, NOW())");
      $stmt->execute(array(":userid"=>$result['id']));
    } else {
      //not logged in as student
      $result['student'] = false;
      $stmt = $this->prepare("SELECT * FROM teachers WHERE name=:name AND (pwenc=SHA2(:pw, 256) OR (pworig=:pw AND pwenc=''))");
      $stmt->execute(array(":name" => $user, ":pw" => $pw));
      $result = $stmt->fetch(PDO::FETCH_ASSOC);
      if (!empty($result)) {
        $result['teacher'] = true;
        //write login-table
        $stmt = $this->prepare("INSERT INTO logins (userid, teacher, dt) VALUES (:userid, TRUE, NOW())");
        $stmt->execute(array(":userid"=>$result['id']));
      } else {
        $result['teacher'] = false;
      }
    }
    return $result;
  }
  
  
  //-----
  //users
  //-----
  
  public function insertUser ($username) {
    $pw = mt_rand(10000,99999);
    $stmt = $this->prepare("INSERT INTO user (name, pworig) VALUES (:n, :p)");
    if ($stmt->execute(array(":n"=>$username, ":p"=>$pw)) == false) {
      return false;
    }
    return $pw;
  }
  
  //settings
  public function setPw ($userid, $pw) {
    $stmt = $this->prepare("UPDATE user SET pwenc=SHA2(:pw, 256) WHERE id=:id");
    $stmt->execute(array(":id" => $userid, ":pw" => $pw));
  }
  
  public function setMail ($userid, $mail) {
    $stmt = $this->prepare("UPDATE user SET mail=:mail WHERE id=:id");
    $stmt->execute(array(":id" => $userid, ":mail" => $mail));
  }
  
  public function setDob ($userid, $date) {
    $stmt = $this->prepare("UPDATE user SET dob=:dob WHERE id=:id");
    $stmt->execute(array(":id"=>$userid, ":dob"=>$date));
  }
  
  public function setProfilePic ($userid, $picname) {
    $stmt = $this->prepare("UPDATE user SET img=:img WHERE id=:id");
    $stmt->execute(array(":id"=>$userid, ":img"=>$picname));
  }
  
  public function setFavClass ($classid) {
    $stmt = $this->prepare("UPDATE user SET bestClass=:class WHERE id=:id");
    $stmt->execute(array(":id"=>$_SESSION['userid'], ":class"=>$classid));
  }
  
  //general
  
  public function getUser ($userid) {
    $stmt = $this->prepare("SELECT * FROM user WHERE id=:id");
    $stmt->execute(array(":id" => $userid));
    return $stmt->fetch(PDO::FETCH_ASSOC);
  }
  
  public function getUsers () {
    $stmt = $this->prepare("SELECT * FROM user");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }
  
  public function getEmails () {
    $stmt = $this->prepare("SELECT mail,name FROM user WHERE mail!=''");
    $stmt->execute(array());
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }
  
  //ui-specific
  public function getProfile ($userid) {
    $stmt = $this->prepare("SELECT u.*,c.name AS bestClassName FROM user u 
    LEFT JOIN classes c ON (u.bestClass=c.id) 
    WHERE u.id=:id");
    $stmt->execute(array(":id" => $userid));
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $cmStmt;
    if ($_SESSION['admin']) {
      //admin
      $cmStmt = $this->prepare(
        "SELECT c.*,u.name FROM comments c LEFT JOIN user u ON (c.userid=u.id) WHERE c.target=:t AND c.type=0");
      $cmStmt->execute(array(":t" => $userid));
      $result['comments'] = $cmStmt->fetchAll(PDO::FETCH_ASSOC);
      $cmStmt = $this->prepare(
        "SELECT c.*,t.name FROM comments c LEFT JOIN teachers t ON (c.userid=t.id) WHERE c.target=:t AND c.type=10");
      $cmStmt->execute(array(":t" => $userid));
      $result['teachercomments'] = $cmStmt->fetchAll(PDO::FETCH_ASSOC);
    } elseif ($userid !== $_SESSION['userid'] && $_SESSION['student']) {
      //other profile and !admin
      $cmStmt = $this->prepare(
        "SELECT
        c.*,u.name
        FROM comments c LEFT JOIN user u ON (c.userid=u.id) WHERE c.userid=:i AND c.target=:t AND c.type=0");
      $cmStmt->execute(array(":i" => $_SESSION['userid'], ":t" => $userid));
      $result['comments'] = $cmStmt->fetchAll(PDO::FETCH_ASSOC);
    } elseif ($_SESSION['teacher']) {
      $cmStmt = $this->prepare(
        "SELECT
        c.*,u.name
        FROM comments c LEFT JOIN teachers u ON (c.userid=u.id) WHERE c.userid=:i AND c.target=:t AND c.type=10");
      $cmStmt->execute(array(":i" => $_SESSION['userid'], ":t" => $userid));
      $result['teachercomments'] = $cmStmt->fetchAll(PDO::FETCH_ASSOC);
    }
    $stmt = $this->prepare("SELECT c.id,c.name FROM class_members cm 
    INNER JOIN classes c ON cm.classid=c.id 
    WHERE cm.userid=:id");
    $stmt->execute(array(":id"=>$userid));
    $result['classes'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if ($result['img'] == NULL) {
      $result['img'] = 'person.jpg';
    }
    return $result;
  }
  
  
  public function getLogins ($uid, $teacher=false) {
    $stmt = $this->prepare("SELECT * FROM logins WHERE userid=:uid AND teacher=:teacher ORDER BY dt DESC");
    $stmt->execute(array(":uid"=>$uid, ":teacher"=>$teacher));
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }
  
  
  //comments
  //types:
  //0-10 by student:
  //0=student, 1=class, 2=classCite, 3=teacher, 4=teacherCite
  //10+ by teacher:
  //10=student, 11=class, 12=classCite
  public function getComment ($cid) {
    $stmt = $this->prepare("SELECT * FROM comments WHERE id=:id");
    $stmt->execute(array(":id"=>$cid));
    $r = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($r['type'] >= 10) {
      $stmt = $this->prepare("SELECT name FROM teachers WHERE id=:id");
      $stmt->execute(array(":id"=>$r['teacherid']));
      $tr = $stmt->fetch(PDO::FETCH_ASSOC);
      $r['name'] = $tr['name'];
    } else {
      $stmt = $this->prepare("SELECT name FROM user WHERE id=:id");
      $stmt->execute(array(":id"=>$r['userid']));
      $ur = $stmt->fetch(PDO::FETCH_ASSOC);
      $r['name'] = $ur['name'];
    }
    if (($r['userid'] === $_SESSION['userid'] && (($_SESSION['student'] && $r['type'] < 10) || ($_SESSION['teacher'] && $r['type'] >= 10))) || $_SESSION['admin']) {
      return $r;
    }
    return false;
  }
  
  public function comment ($target, $text, $userid, $type=0, $source="") {
    //dont comment your own profile
    if ($target == $_SESSION['userid'] && !$_SESSION['admin'] && $_SESSION['student'] && $type===0) return false;
    if ($_SESSION['student'] && $type>9) return false;
    if ($_SESSION['teacher'] && $type<10) return false;
    $sql = "INSERT INTO comments (userid, target, type, content, source, time) VALUES (:u, :ta, :ty, :co, :src, NOW())";
    $stmt = $this->prepare($sql);
    $stmt->execute(array(":u"=>$userid, ":ta"=>$target, ":ty"=>$type, ":co"=>$text, ":src"=>$source));
    return true;
  }
  
  public function myComments ($type=0) {
    $sql = "SELECT c.*, u.name FROM comments c LEFT JOIN user u ON (c.target=u.id) WHERE userid=:user AND type=0";
    if ($_SESSION['student']) {
      if ($type === 1) {
        $sql = "SELECT c.*, cl.name FROM comments c LEFT JOIN classes cl ON (c.target=cl.id) WHERE userid=:user AND type=1";
      } elseif ($type === 2) {
        $sql = "SELECT c.*, cl.name FROM comments c LEFT JOIN classes cl ON (c.target=cl.id) WHERE userid=:user AND type=2";
      } elseif ($type === 3) {
        $sql = "SELECT c.*, t.name FROM comments c LEFT JOIN teachers t ON (c.target=t.id) WHERE userid=:user AND type=3";
      } elseif ($type === 4) {
        $sql = "SELECT c.*, t.name FROM comments c LEFT JOIN teachers t ON (c.target=t.id) WHERE userid=:user AND type=4";
      } elseif ($type === 5) {
        $sql = "SELECT c.* FROM comments c WHERE userid=:user AND type=5";
      }
    } else {
      //teacher
      $sql = "SELECT c.*, u.name FROM comments c LEFT JOIN user u ON (c.target=u.id) WHERE userid=:user AND type=10";
      if ($type === 11) {
        $sql = "SELECT c.*, cl.name FROM comments c LEFT JOIN classes cl ON (c.target=cl.id) WHERE userid=:user AND type=11";
      } elseif ($type === 12) {
        $sql = "SELECT c.*, cl.name FROM comments c LEFT JOIN classes cl ON (c.target=cl.id) WHERE userid=:user AND type=12";
      }
    }
    $stmt = $this->prepare($sql);
    $stmt->execute(array(":user"=>$_SESSION['userid']));
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }
  
  public function deleteComment ($cid) {
    $stmt = $this->prepare("SELECT userid,type, target FROM comments WHERE id=:id");
    $stmt->execute(array(":id"=>$cid));
    $r = $stmt->fetch(PDO::FETCH_ASSOC);
    $this->log($_SESSION['username'] . "deleted comment by " . $r['userid'] . " of type " . $r['type'] . " on " . $r['target']);
    if (($r['userid'] === $_SESSION['userid'] && (($_SESSION['student'] && $r['type'] < 10) || $_SESSION['teacher'] && $r['type'] >= 10)) || $_SESSION['admin']) {
      $stmt = $this->prepare("DELETE FROM comments WHERE id=:id");
      $stmt->execute(array(":id"=>$cid));
      return true;
    }
    return false;
  }
  
  public function getOtherComments () {
    $stmt = $this->prepare("SELECT c.*, u.name FROM comments c LEFT JOIN user u ON u.id=c.userid WHERE type=5");
    $admincomment = " AND uaccess.id=".$_SESSION['userid'];
      if ($_SESSION['admin']) {
        $admincomment = "";
      }
      $stmt = $this->prepare("SELECT c.*,u.name,uaccess.id AS canedit FROM comments c
      LEFT JOIN user u ON c.userid=u.id LEFT JOIN user uaccess ON uaccess.id=c.userid" . $admincomment . " WHERE type=5");
      $stmt->execute(array());
      return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }
  
  
  //classes
  public function insertClass ($name, $teacherid) {
    $stmt = $this->prepare("INSERT INTO classes (name, teacherid) VALUES (:n, :t)");
    if ($stmt->execute(array(":n"=>$name, ":t"=>$teacherid)) == false) {
      return false;
    }
    return true;
  }
  
  public function setClassTeacher ($cid, $tid) {
    $stmt = $this->prepare("UPDATE classes SET teacherid=:t WHERE id=:id");
    $stmt->execute(array(':id'=>$cid, ':t'=>$tid));
  }
  
  public function getClass ($id) {
    $stmt = $this->prepare("SELECT c.*,t.dispname AS teacher FROM classes c LEFT JOIN teachers t ON t.id=c.teacherid WHERE c.id=:id");
    $stmt->execute(array(":id"=>$id));
    $r = $stmt->fetch(PDO::FETCH_ASSOC);
    $stmt = $this->prepare("SELECT COUNT(*) AS count FROM class_members WHERE classid=:id");
    $stmt->execute(array(":id"=>$id));
    $tmp = $stmt->fetch(PDO::FETCH_ASSOC);
    $r['studentcount'] = $tmp['count'];
    $stmt = $this->prepare("SELECT u.id,u.name FROM class_members cm
    LEFT JOIN user u ON cm.userid=u.id
    WHERE classid=:id");
    $stmt->execute(array(":id"=>$id));
    $r['students'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if ($_SESSION['student']) {
      $admincomment = " AND uaccess.id=".$_SESSION['userid'];
      if ($_SESSION['admin']) {
        $admincomment = "";
      }
      $stmt = $this->prepare("SELECT c.*,u.name,uaccess.id AS canedit FROM comments c
      LEFT JOIN user u ON (c.userid=u.id) LEFT JOIN user uaccess ON (uaccess.id=c.userid" . $admincomment . ") WHERE target=:id AND type=1");
      $stmt->execute(array(":id"=>$id));
      $r['comments'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
      $stmt = $this->prepare("SELECT c.*,u.name,uaccess.id AS canedit FROM comments c
      LEFT JOIN user u ON (c.userid=u.id) LEFT JOIN user uaccess ON (uaccess.id=c.userid" . $admincomment . ") WHERE target=:id AND type=2");
      $stmt->execute(array(":id"=>$id));
      $r['cites'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    if ($_SESSION['admin'] || $_SESSION['teacher']) {
      $admincomment = " AND uaccess.id=".$_SESSION['userid'];
      if ($_SESSION['admin']) {
        $admincomment = "";
      }
      $stmt = $this->prepare("SELECT c.*,u.name FROM comments c
      LEFT JOIN teachers u ON (c.userid=u.id) LEFT JOIN teachers uaccess ON (uaccess.id=c.userid" . $admincomment . ") WHERE target=:id AND type=11 AND uaccess.id IS NOT NULL");
      $stmt->execute(array(":id"=>$id));
      $r['teachercomments'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
      $stmt = $this->prepare("SELECT c.*,u.name FROM comments c
      LEFT JOIN teachers u ON (c.userid=u.id) LEFT JOIN teachers uaccess ON (uaccess.id=c.userid" . $admincomment . ") WHERE target=:id AND type=12 AND uaccess.id IS NOT NULL");
      $stmt->execute(array(":id"=>$id));
      $r['teachercites'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    return $r;
  }
  
  public function getClasses ($orderbyuser=0) {
    $stmt = $this->prepare("SELECT c.*,cm.userid,t.dispname AS teacher FROM classes c
    LEFT JOIN teachers t ON t.id=c.teacherid
    LEFT JOIN class_members cm ON (c.id=cm.classid && cm.userid=:userid) ORDER BY cm.userid DESC");
    $stmt->execute(array(":userid"=>$orderbyuser));
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }
  
  public function isClassMember ($cid, $uid) {
    $stmt = $this->prepare("SELECT COUNT(*) AS ismember FROM class_members WHERE classid=:cid AND userid=:uid");
    $stmt->execute(array(":cid"=>$cid, ":uid"=>$uid));
    $tmp = $stmt->fetch(PDO::FETCH_ASSOC);
    return $tmp['ismember'] ? true : false;
  }
  
  public function rmClassMember ($cid, $uid) {
    $stmt;
    if ($this->isClassMember($cid, $uid) == true) {
      $stmt = $this->prepare("DELETE FROM class_members WHERE classid=:c AND userid=:u");
      $stmt->execute(array(":c"=>$cid, ":u"=>$uid));
    }
  }
  
  public function addClassMember ($cid, $uid) {
    $stmt;
    if ($this->isClassMember($cid, $uid) == false) {
      $stmt = $this->prepare("INSERT INTO class_members (classid, userid) VALUES (:c, :u)");
      $stmt->execute(array(":c"=>$cid, ":u"=>$uid));
    }
  }
  
  
  //surveys
  public function getSurveys ($userid=0) {
    $stmt = $this->prepare("
      SELECT s.*,sui.uid FROM surveys s 
      LEFT JOIN 
        (SELECT sa.surveyid AS sid ,sv.userid AS uid 
        FROM survey_answers sa,survey_votes sv 
        WHERE sv.answerid=sa.id AND sv.userid=:usr) 
      AS sui ON (sui.sid=s.id)
      ORDER BY CASE WHEN sui.uid IS NULL THEN 0 ELSE 1 END, s.id
    ");
    $stmt->execute(array(":usr"=>$userid));
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }
  
  public function getSurveysLeft () {
    $stmt = $this->prepare("
      SELECT COUNT(*) AS c FROM surveys s 
      LEFT JOIN 
        (SELECT sa.surveyid AS sid ,sv.userid AS uid 
        FROM survey_answers sa,survey_votes sv 
        WHERE sv.answerid=sa.id AND sv.userid=:usr) 
      AS sui ON (sui.sid=s.id)
      WHERE sui.uid IS NULL
    ");
    $stmt->execute(array(":usr"=>$_SESSION['userid']));
    $r = $stmt->fetch(PDO::FETCH_ASSOC);
    return $r['c']>0 ? $r['c'] : '';
  }
  
  public function getSurvey ($sid, $uid=0) {
    $stmt = $this->prepare("
      SELECT s.*,v.votes AS votes FROM surveys s
      LEFT JOIN 
          (SELECT COUNT(*) as votes, sa.surveyid as sid FROM survey_votes sv 
            LEFT JOIN survey_answers sa ON sa.id=sv.answerid WHERE sa.surveyid=:sid) 
      AS v ON v.sid=s.id
      WHERE id=:sid");
    $stmt->execute(array(":sid"=>$sid));
    $r = $stmt->fetch(PDO::FETCH_ASSOC);
    $stmt = $this->prepare("
    SELECT sa.*,v.count,u.voted FROM survey_answers sa 
    LEFT JOIN
      (SELECT COUNT(*) AS count,answerid FROM survey_votes GROUP BY answerid)
    AS v ON (v.answerid=sa.id)
    LEFT JOIN
      (SELECT answerid,1 AS voted FROM survey_votes WHERE userid=:uid)
    AS u ON (u.answerid=sa.id)
    WHERE surveyid=:sid
    ORDER BY v.count DESC");
    $stmt->execute(array(":sid"=>$sid, ":uid"=>$uid));
    $r['answers'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $r;
  }
  
  public function addSurvey ($name) {
    $stmt = $this->prepare("INSERT INTO surveys (name) VALUES (:n)");
    $stmt->execute(array(":n"=>$name));
  }
  
  public function deleteSurvey ($sid) {
    $stmt = $this->prepare("SELECT name FROM surveys WHERE id=:sid");
    $stmt->execute(array(":sid"=>$sid));
    $survey = $stmt->fetch(PDO::FETCH_ASSOC);
    $this->log($_SESSION['username'] . " deleted survey: " . $survey['name']);
    $sstmt = $this->prepare("DELETE FROM surveys WHERE id=:sid");
    $aqstmt = $this->prepare("SELECT id FROM survey_answers WHERE surveyid=:sid");
    $vstmt = $this->prepare("DELETE FROM survey_votes WHERE surveyid=:aid");
    $adstmt = $this->prepare("DELETE FROM survey_answers WHERE surveyid=:sid");
    $sstmt->execute(array(":sid"=>$sid));
    $aqstmt->execute(array(":sid"=>$sid));
    while ($id = $aqstmt->fetch(PDO::FETCH_ASSOC)) {
      $vstmt->execute(array(":aid"=>$id['id']));
    }
    $adstmt->execute(array(":sid"=>$sid));
  }
  
  public function getVote ($sid, $uid) {
    $stmt = $this->prepare("
    SELECT sv.id AS voteid FROM survey_answers sa,survey_votes sv 
    WHERE sa.surveyid=:sid AND sv.userid=:uid AND sv.answerid=sa.id");
    $stmt->execute(array(":sid"=>$sid, ":uid"=>$uid));
    $t = $stmt->fetch(PDO::FETCH_ASSOC);
    return $t['voteid'] ? $t['voteid'] : 0;
  }
  
  public function deleteVote ($sid, $uid) {
    $votedid = $this->getVote($sid, $uid);
    if ($votedid) {
      //delete previous vote
      $stmt = $this->prepare("DELETE FROM survey_votes WHERE id=:vid");
      $stmt->execute(array(":vid"=>$votedid));
    }
  }
  
  public function vote ($sid, $aid, $uid) {
    $this->deleteVote($sid, $uid);
    $stmt = $this->prepare("INSERT INTO survey_votes (answerid, userid) VALUES (:a, :u)");
    $stmt->execute(array(":a"=>$aid, ":u"=>$uid));
  }
  
  public function addAnswer ($sid, $content, $uid) {
    $stmt = $this->prepare("INSERT INTO survey_answers (surveyid, content) VALUES (:s, :c)");
    $stmt->execute(array(":s"=>$sid, ":c"=>$content));
    $aid = $this->lastInsertId();
    $this->vote($sid, $aid, $uid);
  }
  
  public function deleteAnswer($aid, $mergeid=false) {
    $stmt = $this->prepare("
    SELECT sa.*,v.votes FROM survey_answers sa
    LEFT JOIN (SELECT answerid,COUNT(*) AS votes FROM survey_votes WHERE answerid=:aid) AS v ON v.answerid=sa.id
    WHERE id=:aid
    ");
    $stmt->execute(array(":aid"=>$aid));
    $sa = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$mergeid) {
      $this->log($_SESSION['username'] . " deleted survey_answer to " . $sa['surveyid'] . ": " . $sa['content'] . " (" . $sa['votes'] . " votes)");
    } else {
      $this->log($_SESSION['username'] . " merged survey_answer to " . $sa['surveyid'] . ": " . $sa['content'] . " with another answer (id: " . $mergeid . ")");
    }
    $stmt = $this->prepare("DELETE FROM survey_answers WHERE id=:aid; DELETE FROM survey_votes WHERE answerid=:aid");
    $stmt->execute(array(":aid"=>$aid));
  }
  
  public function mergeSurveyAns($aiddel, $aidmerge) {
    $stmt = $this->prepare('UPDATE survey_votes SET answerid=:mid WHERE answerid=:did');
    $stmt->execute(array(":did"=>$aiddel, ":mid"=>$aidmerge));
    $this->deleteAnswer($aiddel, $aidmerge);
  }
  
  
  //teachers
  public function insertTeacher ($name) {
    $pw = mt_rand(10000,99999);
    $stmt = $this->prepare("INSERT INTO teachers (name, dispname, pworig) VALUES (:n, :n, :p)");
    if ($stmt->execute(array(":n"=>$name, ":p"=>$pw)) == false) {
      return false;
    }
    return $pw;
  }
  
  public function changeTeacherPw ($pw, $tid=0) {
    if ($_SESSION['teacher']) {
      $tid = $_SESSION['userid'];
    } elseif (!$_SESSION['admin']) {
      return false;
    }
    $stmt = $this->prepare("UPDATE teachers SET pwenc=SHA2(:pw, 256) WHERE id=:id");
    $stmt->execute(array(":id" => $tid, ":pw" => $pw));
  }
  
  public function getTeachers () {
    $stmt = $this->prepare("SELECT * FROM teachers");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }
  
  public function getTeacher ($userid) {
    $stmt = $this->prepare("SELECT * FROM teachers WHERE id=:id");
    $stmt->execute(array(":id" => $userid));
    return $stmt->fetch(PDO::FETCH_ASSOC);
  }
  
  public function getTeacherProfile ($id) {
    $stmt = $this->prepare("SELECT * FROM teachers WHERE id=:id");
    $stmt->execute(array(":id"=>$id));
    $r = $stmt->fetch(PDO::FETCH_ASSOC);
    $admincomment = " AND uaccess.id=".$_SESSION['userid'];
    if ($_SESSION['admin']) {
      $admincomment = "";
    }
    $stmt = $this->prepare("SELECT c.*,u.name,uaccess.id AS canedit FROM comments c
    LEFT JOIN user u ON (c.userid=u.id) LEFT JOIN user uaccess ON (uaccess.id=c.userid" . $admincomment . ") WHERE target=:id AND type=3");
    $stmt->execute(array(":id"=>$id));
    $r['comments'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt = $this->prepare("SELECT c.*,u.name,uaccess.id AS canedit FROM comments c
    LEFT JOIN user u ON (c.userid=u.id) LEFT JOIN user uaccess ON (uaccess.id=c.userid" . $admincomment . ") WHERE target=:id AND type=4");
    $stmt->execute(array(":id"=>$id));
    $r['cites'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $r;
  }
  
  public function changeTeacherName ($id, $newName) {
    $stmt = $this->prepare("SELECT dispname FROM teachers WHERE id=:id");
    $stmt->execute(array(":id"=>$id));
    $t = $stmt->fetch(PDO::FETCH_ASSOC);
    $this->log($_SESSION['username'] . " changed teacherName " . $t['name'] . " to " . $newName);
    $stmt = $this->prepare("UPDATE teachers SET dispname=:nn WHERE id=:id");
    $stmt->execute(array(":nn"=>$newName, ":id"=>$id));
  }
  
  
  
  
  //news
  public function getNews() {
    $stmt = $this->prepare("SELECT * FROM news ORDER BY time DESC");
    $stmt->execute(array());
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }
  
  public function insertNews($headline, $content) {
    if (!$_SESSION['admin']) return;
    $stmt = $this->prepare("INSERT INTO news (headline, content, time) VALUES (:h, :c, NOW())");
    $stmt->execute(array(":h"=>$headline, ":c"=>$content));
  }
  
  
  
  //queries
  public function getQueries() {
    $stmt = $this->prepare("SELECT * FROM queries");
    $stmt->execute(array());
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }
  
  public function getQuery($qid) {
    $stmt = $this->prepare("SELECT * FROM queries WHERE id=:id");
    $stmt->execute(array(":id"=>$qid));
    return $stmt->fetch(PDO::FETCH_ASSOC);
  }
  
  public function addQuery($name, $query) {
    $stmt = $this->prepare("INSERT INTO queries (name, querystring) VALUES (:n, :q)");
    $stmt->execute(array(":n"=>$name, ":q"=>$query));
  }
  
  public function deleteQuery($qid) {
    $stmt = $this->prepare("DELETE FROM queries WHERE id=:id");
    $stmt->execute(array(":id"=>$qid));
  }
  
  public function execQuery($sql) {
    $this->log($_SESSION['username'] . " executed: " . $sql);
    if ($_SESSION['userid'] != 65) {
      $result = $this->query($sql);
      if ($response = $result->fetchAll(PDO::FETCH_ASSOC)) {
        return json_encode($response);
      }
    } else { //Steffi can not delete random stuff :D
      $npdo = new self(true);
      $result = $npdo->query($sql);
      if ($response = $result->fetchAll(PDO::FETCH_ASSOC)) {
        return json_encode($response);
      }
    }
  }
  
  
  
  //specials as teacher
  public function getClassAsTeacher($id) {
    $stmt = $this->prepare("SELECT * FROM classes WHERE id=:id");
    $stmt->execute(array(":id"=>$id));
    $r = $stmt->fetch(PDO::FETCH_ASSOC);
    $stmt = $this->prepare("SELECT COUNT(*) AS count FROM class_members WHERE classid=:id");
    $stmt->execute(array(":id"=>$id));
    $tmp = $stmt->fetch(PDO::FETCH_ASSOC);
    $r['studentcount'] = $tmp['count'];
    $stmt = $this->prepare("SELECT u.id,u.name FROM class_members cm
    LEFT JOIN user u ON cm.userid=u.id
    WHERE classid=:id");
    $stmt->execute(array(":id"=>$id));
    $r['students'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $admincomment = " AND uaccess.id=".$_SESSION['userid'];
    $stmt = $this->prepare("SELECT c.*,u.name FROM comments c
    LEFT JOIN user u ON (c.userid=u.id) WHERE target=:id AND type=11");
    $stmt->execute(array(":id"=>$id));
    $r['comments'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt = $this->prepare("SELECT c.*,u.name FROM comments c
    LEFT JOIN user u ON (c.userid=u.id) WHERE target=:id AND type=12");
    $stmt->execute(array(":id"=>$id));
    $r['cites'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $r;
  }
  
  
  
  
  //Lena und Jana sind einfach zu fucking faul die ganze Scheisse selbst zu kopieren
  public function getAllCites () {
    $stmt = $this->prepare("SELECT content,source FROM comments WHERE type=2");
    $stmt->execute(array());
    $result = array();
    $result['cl'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt = $this->prepare("SELECT content,source FROM comments WHERE type=4");
    $stmt->execute(array());
    $result = array();
    $result['t'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $result;
  }
  
}
?>