<?php
class stats{

    protected $processedNum=0;
    protected $lastDeadLinks="";
    protected $activeLinkNum=0;
    protected $deletedLinkNum=0;
    protected $maxDbId=0;

    public function processedNum($processed=0){
        if($processed > 0 ){
            $this->processedNum += $processed;
        }
        return $this->processedNum;   
    }

    public function lastDeadLinks($lastDeadLinks=""){
        if($lastDeadLinks != ""){
            $this->lastDeadLinks = $lastDeadLinks;
        }
        return $this->lastDeadLinks;   
    }

    public function activeLinkNum($active=0){
        if($active > 0){
            $this->activeLinkNum += $active;
        }
        return $this->activeLinkNum;   
    }

    public function deletedLinkNum($deleted=0){
        if($deleted > 0){
            $this->deletedLinkNum += $deleted;
        }
        return $this->deletedLinkNum;   
    }

    public function maxDbId($maxDbId=0){
        if($maxDbId > 0){
            $this->maxDbId = $maxDbId;
        }
        return $this->maxDbId;   
    }
}

class filehost{
    public $name;
    public $dead=0;
    public $alive=0;
    public $storeLinks="";
    public $processed=0;
    
    public function setName($name){
            $this->name = $name;  
    }
    public function getName(){
        return $this->name ;  
    }

    public function alive($active=0){
        if($active > 0){
            $this->alive += $active;
        }
        return $this->alive;   
    }

    public function dead($dead=0){
        if($dead > 0){
            $this->dead += $dead;
        }
        return $this->dead;   
    }

    public function storeLinks($linkUrl="",$linkID=0){
        if($linkID > 0 ){
            $this->storeLinks[$linkUrl] = $linkID;
        }
        return $this->storeLinks;   
    }
    
    public function clearLinks(){
            $this->storeLinks = ""; 
    }
    
    public function processed($processed=0){
        if($processed > 0 ){
            $this->processed += $processed;
        }
        return $this->processed;   
    }


}
?>
