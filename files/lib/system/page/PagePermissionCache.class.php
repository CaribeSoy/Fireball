<?php
namespace cms\system\page;

use cms\system\cache\builder\PagePermissionCacheBuilder;
use wcf\data\user\User;
use wcf\system\acl\ACLHandler;
use wcf\system\user\storage\UserStorageHandler;
use wcf\system\SingletonFactory;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * @author	Jens Krumsieck
 * @copyright	2014 codeQuake
 * @license	GNU Lesser General Public License <http://www.gnu.org/licenses/lgpl-3.0.txt>
 * @package	de.codequake.fireball
 */

class PagePermissionCache extends SingletonFactory{
    protected $permissions = array();
    
    
    protected function loadPermissions(User $user) {
        $this->permissions[$user->userID] = PagePermissionCacheBuilder::getInstance()->getData($user->getGroupIDs());
        
        if ($user->userID) {
        
            //no cache
            if ($this->permissions[$user->userID] === null) {
                $permissions = array();
                $sql = "SELECT	option_to_user.objectID AS pageID, option_to_user.optionValue,
						acl_option.optionName AS permission, acl_option.categoryName
					FROM	wcf".WCF_N."_acl_option acl_option,
						wcf".WCF_N."_acl_option_to_user option_to_user
					WHERE	acl_option.objectTypeID = ?
						AND option_to_user.optionID = acl_option.optionID
						AND option_to_user.userID = ?";
				$statement = WCF::getDB()->prepareStatement($sql);
				$statement->execute(array(
					ACLHandler::getInstance()->getObjectTypeID('de.codequake.cms.page'),
					$user->userID
				));
                while ($row = $statement->fetchArray()) {
                    $permissions[$row['boardID']][$row['permission']] = $row['optionValue'];
                }
                foreach ($permissions as $pageID => $permission) {
				    foreach ($permission as $name => $value) {
					    $this->permissions[$user->userID][$pageID][$name] = $value;
				    }
			    }
            }
        }
    }
    
    public function getPermissions(User $user) {
		if (!isset($this->permissions[$user->userID])) {
			$this->loadPermissions($user);
		}
		
		return $this->permissions[$user->userID];
	}
}
