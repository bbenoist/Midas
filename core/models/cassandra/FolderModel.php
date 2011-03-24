<?php
require_once BASE_PATH.'/core/models/base/FolderModelBase.php';

/**
 * \class FolderModel
 * \brief Cassandra Model
 */
class FolderModel extends FolderModelBase
{     
 
  /** Get a user by email */
  function getByFolder_id($folderid)
    {
    try 
      {
      $folder = new ColumnFamily($this->database->getDB(), 'folder');
      $folderarray = $folder->get($folderid);      
      // Add the user_id
      $folderarray[$this->_key] = $folderid;
      $dao= $this->initDao('Folder',$userarray);      
      }
    catch(cassandra_NotFoundException $e) 
      {
      return false;  
      }      
    catch(Exception $e) 
      {
      throw new Zend_Exception($e); 
      }  
    return $dao;
    } // end getByFolder_id()
  
  
  
  /** Custom save function*/
  public function save($folder)
    {
    if(!$folder instanceof FolderDao)
      {
      throw new Zend_Exception("Should be a folder.");
      }
/*
   if($folder->getParentId()<=0)
      {
      $rightParent=0;
      }
    else
      {
      $parentFolder=$folder->getParent();
      $rightParent=$parentFolder->getRightIndice();
      }
      
   
  */   

     $rightParent=0;   // REMOVE ME
       
    $data = array();
  
    
    foreach($this->_mainData as $key => $var)
      {
      if(isset($folder->$key))
        {
        $data[$key] = $folder->$key;
        }
      if($key=='right_indice')
        {
        $folder->$key=$rightParent+1;
        $data[$key]=$rightParent+1;
        }
      if($key=='left_indice')
        {
        $data[$key]=$rightParent;
        }
      }

    if(isset($data['folder_id']))
      {
      $key = $data['folder_id'];
      unset($data['folder_id']);
      unset($data['left_indice']);
      unset($data['right_indice']);
      
      $column_family = new ColumnFamily($this->database->getDB(), 'folder');
      $column_family->insert($key,$data); 
      return $key;
      }
    else
      {
      /*$this->_db->update('folder', array('right_indice'=> new Zend_Db_Expr('2 + right_indice')),
                          array('right_indice >= ?'=>$rightParent));
      $this->_db->update('folder', array('left_indice'=> new Zend_Db_Expr('2 + left_indice')),
                          array('left_indice >= ?'=>$rightParent));
      $insertedid = $this->insert($data);
      */
      $column_family = new ColumnFamily($this->database->getDB(), 'folder');
      $uuid = CassandraUtil::uuid1();    
      $column_family->insert($uuid,$data);      
      $folder->folder_id = $uuid;
      $folder->saved=true;
      return true;
      }
    } // end method save  
    
    
} // end class FolderModel
?>
