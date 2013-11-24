<?php
namespace Craft;

class SenorForm_FieldRecord extends FieldRecord
{
	private $content = '';
	
    public function getTableName()
    {
        return 'senorform_fields';
    }
    
	public function setContent($content)
	{
		$this->content = $content;
	}
	
	public function getContent()
	{
        $arr = json_decode($this->content);
        if( is_array($arr))
        {
            $content = array();
            foreach($arr as $key=>$content_value)
            {
                $content[] = $key . ':' . $content_value;
            }
            $this->content = implode("<br/>", $content);
        }
		return $this->content;
	}

    
    /**
     * @access protected
     * @return array
     */
    protected function defineAttributes()
    {
    	return array(
    			'name'         => array(AttributeType::Name, 'required' => true),
    			'handle'       => array(AttributeType::Handle, 'maxLength' => 64, 'required' => true, 'reservedWords' => $this->reservedHandleWords),
    			'instructions' => array(AttributeType::String, 'column' => ColumnType::Text),
    			'translatable' => AttributeType::Bool,
    			'type'         => array(AttributeType::ClassName, 'required' => true),
    			'settings'     => AttributeType::Mixed,
    			'validation'     => array(AttributeType::String, 'column' => ColumnType::Text),
    	);
    }

    /**
     * @return array
     */
    public function defineRelations()
    {
        return array(
            'form' => array(static::BELONGS_TO, 'SenorForm_FormRecord', 'onDelete' => static::CASCADE),
        );
    }
    
    public function saveField()
    {
    	
    }
    
}
