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
 * @package    Chat_Form
 * @copyright  Copyright (C) 2005-2010  Preceptor Educação a Distância Ltda. <http://www.preceptoead.com.br>
 * @license    http://www.gnu.org/licenses/  GNU GPL
 */
class Chat_Form_Room extends Zend_Form
{
    /**
     * (non-PHPdoc)
     * @see Zend_Form#init()
     */
    public function init()
    {
        $this->addElementPrefixPath('Tri_Filter', 'Tri/Filter', 'FILTER');
        
        $table  = new Tri_Db_Table('chat_room');

        $validators    = $table->getValidators();
        $filters       = $table->getFilters();
        $statusOptions = array('open' => 'open', 'close' => 'close');

        $this->setAction('chat/room/save')
             ->setMethod('post')
             ->setAttrib('enctype', 'multipart/form-data');

        $id = new Zend_Form_Element_Hidden('id');
        $id->addValidators($validators['id'])
           ->addFilters($filters['id'])
           ->removeDecorator('Label')
           ->removeDecorator('HtmlTag');

        $filters['name'][] = 'StripTags';
        $title = new Zend_Form_Element_Text('title');
        $title->setLabel('Title')
             ->setAttrib('size', '55')
             ->addValidators($validators['title'])
             ->addFilters($filters['title']);

        $description = new Zend_Form_Element_Textarea('description');
        $description->setLabel('Description')
                 ->addValidators($validators['description'])
                 ->addFilters($filters['description'])
                 ->setAttrib('rows', 10);

        $max = new Zend_Form_Element_Text('max_student');
        $max->setLabel('Max student')
             ->addValidators($validators['max_student'])
             ->addFilters($filters['max_student']);

        if (!$statusOptions || isset($statusOptions[''])) {
            $status = new Zend_Form_Element_Text('status');
        } else {
            $statusOptions = array_unique($statusOptions);
            $status        = new Zend_Form_Element_Select('status');
            $status->addMultiOptions($statusOptions)
                   ->setRegisterInArrayValidator(false);
        }
        $status->setLabel('Status')
               ->addValidators($validators['status'])
               ->addFilters($filters['status']);

        $this->addElement($id)
             ->addElement($title)
             ->addElement($description)
             //->addElement($max)
             ->addElement($status)
             ->addElement('submit', 'Save');
   }
}
