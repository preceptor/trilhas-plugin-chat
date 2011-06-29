<?php
/**
 * Trilhas - Learning Management System
 * Copyright (C) 2005-2010  Preceptor Educação a Distância Ltda. <http://www.preceptoead.com.br>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * @category   Chat
 * @package    Chat_Plugin
 * @copyright  Copyright (C) 2005-2010  Preceptor Educação a Distância Ltda. <http://www.preceptoead.com.br>
 * @license    http://www.gnu.org/licenses/  GNU GPL
 */
class Chat_Plugin extends Tri_Plugin_Abstract
{
    protected $_name = "chat";
    
    protected function _createDb()
    {
        $sql = "CREATE TABLE IF NOT EXISTS `chat` (
                  `id` bigint(20) NOT NULL AUTO_INCREMENT,
                  `sender` bigint(20) NOT NULL,
                  `receiver` bigint(20) NOT NULL,
                  `description` text,
                  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                  PRIMARY KEY (`id`),
                  KEY `sender` (`sender`),
                  KEY `receiver` (`receiver`)
                ) ENGINE=InnoDB;

                CREATE TABLE IF NOT EXISTS `chat_room` (
                  `id` bigint(20) NOT NULL AUTO_INCREMENT,
                  `classroom_id` bigint(20) NOT NULL,
                  `user_id` bigint(20) NOT NULL,
                  `title` varchar(255) NOT NULL,
                  `description` text,
                  `max_student` int(10) DEFAULT NULL,
                  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                  `status` enum('open','close') NOT NULL,
                  PRIMARY KEY (`id`),
                  KEY `classroom_id` (`classroom_id`)
                ) ENGINE=InnoDB;

                CREATE TABLE IF NOT EXISTS `chat_room_message` (
                  `id` bigint(20) NOT NULL AUTO_INCREMENT,
                  `chat_room_id` bigint(20) NOT NULL,
                  `user_id` bigint(20) NOT NULL,
                  `description` text,
                  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                  `status` enum('logged','message') NOT NULL,
                  PRIMARY KEY (`id`),
                  KEY `chat_room_id` (`chat_room_id`),
                  KEY `user_id` (`user_id`)
                ) ENGINE=InnoDB;

                ALTER TABLE `chat`
                  ADD CONSTRAINT `chat_ibfk_1` FOREIGN KEY (`sender`) REFERENCES `user` (`id`),
                  ADD CONSTRAINT `chat_ibfk_2` FOREIGN KEY (`receiver`) REFERENCES `user` (`id`);

                ALTER TABLE `chat_room`
                  ADD CONSTRAINT `chat_room_ibfk_1` FOREIGN KEY (`classroom_id`) REFERENCES `classroom` (`id`);

                ALTER TABLE `chat_room_message`
                  ADD CONSTRAINT `chat_room_message_ibfk_1` FOREIGN KEY (`chat_room_id`) REFERENCES `chat_room` (`id`),
                  ADD CONSTRAINT `chat_room_message_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`);";
        
        $this->_getDb()->query($sql);
    }

    public function install()
    {
        $this->_createDb();
    }

    public function activate()
    {
        $this->_addClassroomMenuItem('communication','message','chat/message/index');
        $this->_addClassroomMenuItem('communication','chat','chat/room/index');
        $this->_addAclItem('chat/chat/index', 'identified');
        $this->_addAclItem('chat/chat/form', 'identified');
        $this->_addAclItem('chat/chat/save', 'identified');
        $this->_addAclItem('chat/chat/find', 'identified');
        $this->_addAclItem('chat/room/index', 'identified');
        $this->_addAclItem('chat/room/form', 'teacher, coordinator, institution');
        $this->_addAclItem('chat/room/save', 'teacher, coordinator, institution');
        $this->_addAclItem('chat/room/delete', 'teacher, coordinator, institution');
        $this->_addAclItem('chat/room/block', 'teacher, coordinator, institution');
        $this->_addAclItem('chat/room/unblock', 'teacher, coordinator, institution');
        $this->_addAclItem('chat/room/live', 'identified');
        $this->_addAclItem('chat/room/live-save', 'identified');
        $this->_addAclItem('chat/room/view', 'identified');
        $this->_addAclItem('chat/message/index', 'identified');
        $this->_addAclItem('chat/message/view', 'identified');
        $this->_addAclItem('chat/message/reply', 'identified');
        $this->_addAclItem('chat/message/save', 'identified');
        $this->_addAclItem('chat/message/delete', 'identified');
    }

    public function desactivate()
    {
        $this->_removeClassroomMenuItem('communication','message');
        $this->_removeClassroomMenuItem('communication','chat');
        $this->_removeAclItem('chat/chat/index');
        $this->_removeAclItem('chat/chat/form');
        $this->_removeAclItem('chat/chat/save');
        $this->_removeAclItem('chat/chat/find');
        $this->_removeAclItem('chat/room/index');
        $this->_removeAclItem('chat/room/form');
        $this->_removeAclItem('chat/room/save');
        $this->_removeAclItem('chat/room/delete');
        $this->_removeAclItem('chat/room/live');
        $this->_removeAclItem('chat/room/live-save');
        $this->_removeAclItem('chat/room/view');
        $this->_removeAclItem('chat/room/block');
        $this->_removeAclItem('chat/room/unblock');
        $this->_removeAclItem('chat/message/index');
        $this->_removeAclItem('chat/message/view');
        $this->_removeAclItem('chat/message/reply');
        $this->_removeAclItem('chat/message/save');
        $this->_removeAclItem('chat/message/delete');
    }
}

