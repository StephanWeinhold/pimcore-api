<?php
use Website\Controller\Action;
use Pimcore\Model\Object;

class WapiController extends Action
{

    /**
     * URL_OF_YOUR_API/$objectType/$arti/{$returnType}/{$language}
     * $returnType = xml|json - if this has only two chars, it will be taken as language and JSON is the default return type
     *
     * @return xml|json
     */
    public function defaultAction()
    {
        $this->disableLayout();

        $settings = array();

        // Check if API-key is valid
        $this->checkApiKey($this->getParam('apiKey'));

        // Set language(s)
        $settings['language'] = $this->handleLanguages(substr($this->getParam('language'), 1));

        // Check for language||returnType and if necessary set JSON as default return type
        $settings['returnType'] = 'json';
        if ($this->getParam('returnType')) {
            if (substr($this->getParam('returnType'), 1) != 'xml' && substr($this->getParam('returnType'), 1) != 'json') {
                $settings['language'] = substr($this->getParam('returnType'), 1);
            } else {
                $settings['returnType'] = substr($this->getParam('returnType'), 1);
            }
        }

        // Check if language is available
        $this->checkIfLanguageIsAvailable($settings['language']);

        if ($this->getParam('objectId')) {
            // Get object
            $objectType = 'Object_' . ucfirst($this->getParam('objectType'));
            try {
                $o = $objectType::getById($this->getParam('objectId'), array('limit' => 1));
            } catch (Zend_Exception $e) {}

            if (empty($o)) {
                throw new Zend_Http_Header_Exception_InvalidArgumentException('No article found with this article number.', 404);
            }

            // Get needed attributes from the object
            $settings['attributes'] = $this->getAttributes($this->getParam('att'), $o, $settings['language']);

            // Send data to output
            switch ($settings['returnType']) {
                case 'json':
                    $this->_helper->json($settings['attributes']);
                case 'xml':
                    $xml = new SimpleXMLElement('<product/>');
                    $this->arrayToXml($settings['attributes'], $xml);
                    $output = $xml->saveXML();
                    header('Content-Type: text/xml');
                    echo($output);
                    die();
                default:
                    throw new Zend_Http_Header_Exception_InvalidArgumentException('Type not supported.', 404);
            }
        } else {
            throw new Zend_Http_Header_Exception_InvalidArgumentException('No article number given.', 404);
        }
    }

    /*
     * Check the API-key
     *
     * @param $apiKey (string)
     * @return true|throws Exception
     */
    private function checkApiKey($apiKey = '')
    {
        $return = FALSE;
        if (HOWEVER_YOUR_APIKEY_IS_BUILT) {
            $return = true;
        }
        if ($return === FALSE) {
            throw new Zend_Http_Header_Exception_InvalidArgumentException('Wrong API-key.', 403);
        }
    }

    /*
     * Check if one or more languages are sent via params and return them.
     *
     * @param $language (string|array)
     * @return $language|throws Exception
     */
    private function handleLanguages($language = '')
    {
        if ($language == '') {
            $language = 'de';
        }
        switch (gettype($language)) {
            case 'string':
                if (strlen($language) == 2) {
                    return $language;
                }
            case 'array':
                $maxLength = max(array_map('strlen', $language));
                if ($maxLength == 2) {
                    return $language;
                }
        }
        throw new Zend_Http_Header_Exception_InvalidArgumentException('Wrong language-parameter.', 404);
    }

    /*
     * Check if the language is supported in pimcore
     *
     * @param $language (string)
     * @return TRUE|throws Exception
     */
    private function checkIfLanguageIsAvailable($language)
    {
        $config = Pimcore_Config::getSystemConfig();
        if ($config) {
            $validLanguages = strval($config->general->validLanguages);
            $availableLanguages = explode(',', $validLanguages);
            if (in_array($language, $availableLanguages)) {
                return TRUE;
            }
        }
        throw new Zend_Http_Header_Exception_InvalidArgumentException('Language not supported.', 404);
    }

    /*
     * Gets all needed attributes from the object
     *
     * @param $attributes (string|array) attributes the API should deliver
     * @param $object (object)
     * @param $language
     *
     * @return $returnAttributes (array)
     */
    private function getAttributes($attributes, $object, $language)
    {
        if (empty($attributes)) {
            $returnAttributes = array(
                // SET YOUR DEFAULT VALUES HERE
            );
        } else {
            if (gettype($attributes) == 'array') {
                $returnAttributes = array();
                foreach ($attributes as $attribute) {
                    $getter = 'get' . ucfirst($attribute);
                    $returnAttribute = $object->$getter($language);
                    if (gettype($returnAttribute) == 'object') {
                        $returnAttributes[$attribute] = $returnAttribute->getName($language);
                    } else {
                        $returnAttributes[$attribute] = $returnAttribute;
                    }
                }
            } else {
                $getter = 'get' . ucfirst($attributes);
                $returnAttribute = $object->$getter($language);
                if (gettype($returnAttribute) == 'object') {
                    $returnAttributes[$attributes] = $returnAttribute->getName($language);
                } else {
                    $returnAttributes[$attributes] = $returnAttribute;
                }
            }
        }
        return $returnAttributes;
    }

    /*
     * Converts array of any depth to an XML-document
     *
     * @param $array (array) - array to convert to XML
     * @param $xml (obj) - XML-element
     *
     * @return manipulates the reference of the XML-element
     */
    function arrayToXml($array, &$xml) {
        foreach($array as $key => $value) {
            if(is_array($value)) {
                if(!is_numeric($key)){
                    $subnode = $xml->addChild($key);
                    $this->arrayToXml($value, $subnode);
                } else{
                    $subnode = $xml->addChild('item' . $key);
                    $this->arrayToXml($value, $subnode);
                }
            } else {
                $xml->addChild($key, htmlspecialchars($value));
            }
        }
    }

}
