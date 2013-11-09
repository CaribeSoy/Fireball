<?php
namespace cms\system\user\activity\event;

use wcf\system\user\activity\event\IUserActivityEvent;
use wcf\system\SingletonFactory;
use wcf\system\WCF;
use wcf\data\comment\CommentList;
use cms\data\page\PageList;

class PageCommentUserActivityEvent extends SingletonFactory implements IUserActivityEvent{
    public function prepare(array $events){
        $objectIDs = array();
        foreach ($events as $event) {
            $objectIDs[] = $event->objectID;
        }
        
        //comments
        $commentList = new CommentList();
        $commentList->getConditionBuilder()->add("comment.commentID IN (?)", array (
        $objectIDs ));
        $commentList->readObjects();
        $comments = $commentList->getObjects();
        
        //get pages
        $pageIDs = array ();
        foreach($comments as $comment) {
            $pageIDs[] = $comment->objectID;
            }
            
        $list = new PageList();
        $list->getConditionBuilder()->add("page.pageID IN (?)", array($pageIDs));
        $list->readObjects();
        $pages = $list->getObjects();
        
        
        foreach($events as $event){
            if(isset($comments[$event->objectID]))
            {
                $comment = $comments[$event->objectID];
                if(isset($pages[$comment->objectID])){
                    $page = $pages[$comment->objectID];
                    $text = WCF::getLanguage()->getDynamicVariable('wcf.user.profile.recentActivity.pageComment', array(
                        'page' => $page));
                    $event->setTitle($text);
                    $event->setDescription($comment->getFormattedMessage());
                    $event->setIsAccessible();
                }
            }
            else {$event->setIsOrphaned();}  
            
        }
        
    }

}