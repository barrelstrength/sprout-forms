<?php
namespace Craft;

class SproutForms_FieldRecord extends FieldRecord
{
	private $content = '';
	
	/**
	 * Return table name
	 *
	 * @return string
	 */
    public function getTableName()
    {
        return 'sproutforms_fields';
    }
    
    /**
     * Set content
     * 
     * @param string $content
     * @return void
     */
	public function setContent($content)
	{
		$this->content = $content;
	}
	
	/**
	 * Return content
	 * 
	 * @return string
	 */
	public function getContent()
	{
        if( is_array($this->content))
        {
            $content = array();
            foreach($this->content as $key=>$content_value)
            {
                $content[] = $content_value;
            }
            $this->content = implode(",", $content);
        }
		return $this->content;
	}

	/**
	 * Define attributes
	 *
	 * @return array
	 */
    protected function defineAttributes()
    {
    	return array(
    			'name'         => array(AttributeType::Name, 'required' => true),
    			'handle'       => array(AttributeType::Handle, 'maxLength' => 64, 'required' => true, 'reservedWords' => $this->reservedHandleWords),
                'context'      => array(AttributeType::String, 'default' => 'global', 'required' => true),
    			'instructions' => array(AttributeType::String, 'column' => ColumnType::Text),
    			'translatable' => AttributeType::Bool,
    			'type'         => array(AttributeType::ClassName, 'required' => true),
    			'settings'     => AttributeType::Mixed,
    			'validation'   => array(AttributeType::String, 'column' => ColumnType::Text),
    	        'sortOrder'    => AttributeType::SortOrder,
    	);
    }

    /**
     * Define relationships
     * 
     * @return array
     */
    public function defineRelations()
    {
        return array(
            'form' => array(static::BELONGS_TO, 'SproutForms_FormRecord', 'onDelete' => static::CASCADE),
        );
    }   
    
    /**
     * Setter
     * 
     * @param string $handle
     * @return void
     */
    public function setOldHandle($handle)
    {
    	$this->oldHandle = $handle;
    }

}
