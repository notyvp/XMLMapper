<?php

class XMLMapperTest extends \PHPUnit_Framework_TestCase
{

    /** @var  \Edujugon\XMLMapper\XMLMapper */
    protected $mapper;

    protected function setUp()
    {
        $this->mapper = new Edujugon\XMLMapper\XMLMapper();
    }

    /** @test */
    public function instance_has_xml_and_object()
    {
        $xml = '<xml><content></content></xml>';

        $obj = simplexml_load_string($xml);

        $this->mapper->loadXML($xml);

        $this->assertEquals($xml,$this->mapper->getXml());

        $this->assertEquals($obj,$this->mapper->getObj());
    }

    /** @test */
    public function get_value_from_content_element()
    {
        $xml = '<xml id="33"><content att="something">zizoo</content></xml>';

        $this->mapper->loadXML($xml);

        $this->assertEquals('zizoo',$this->mapper->getvalue('content'));
    }

    /** @test */
    public function throw_exception_when_path_is_wrong()
    {
        $xml = '<xml id="33"><content att="something"><first><second><my-value>zizoo</my-value></second></first></content></xml>';

        $this->mapper->loadXML($xml);

        $this->expectException(\Edujugon\XMLMapper\Exceptions\XMLMapperException::class);
        $this->expectExceptionMessage('Tag "i-dont-exists" Doesn\'t exist in the provided xml');

        $this->assertEquals('zizoo',$this->mapper->getvalue(['content','i-dont-exists','second','my-value']));
    }

    /** @test */
    public function get_value_from_a_deep_element()
    {
        $xml = '<xml id="33"><content att="something"><first><second><my-value>zizoo</my-value></second></first></content></xml>';

        $this->mapper->loadXML($xml);

        $this->assertEquals('zizoo',$this->mapper->getvalue(['content','first','second','my-value']));
    }

    /** @test */
    public function get_attribute_from_first_node()
    {
        $xml = '<xml id="33"><content att="something"><first><second><extras><extra name="wrong" id="2" dev="other"></extra><extra name="zizoo" id="1" dev="edu"></extra></extras></second></first></content></xml>';

        $this->mapper->loadXML($xml);

        $this->assertEquals('33',$this->mapper->getAttribute('id'));
    }

    /** @test */
    public function get_attribute_from_child_node()
    {
        $xml = '<xml id="33"><content att="something"><first><second><extras><extra name="wrong" id="2" dev="other"></extra><extra name="zizoo" id="1" dev="edu"></extra></extras></second></first></content></xml>';

        $this->mapper->loadXML($xml);

        $this->assertEquals('wrong',$this->mapper->getAttribute('name',['content','first','second','extras','extra']));
    }

    /** @test */
    public function find_value_of_a_element()
    {
        $xml = '<xml id="33"><content att="something"><first><second><edu><e><d><u><edu></edu></u></d></e></edu><my-value>zizoo</my-value></second></first></content></xml>';

        $this->mapper->loadXML($xml);

        $this->assertEquals('zizoo',$this->mapper->findValue('my-value'));
    }

    /** @test */
    public function find_value_returns_null()
    {
        $xml = '<xml id="33"><content att="something"><first><second><edu><e><d><u><edu></edu></u></d></e></edu><my-value>zizoo</my-value></second></first></content></xml>';

        $this->mapper->loadXML($xml);

        $this->assertNull($this->mapper->findValue('no-exists'));
    }

    /** @test */
    public function find_attribute_of_a_element()
    {
        $xml = '<xml id="33"><content att="something"><first><second><my-value name="zizoo"></my-value></second></first></content></xml>';

        $this->mapper->loadXML($xml);

        $this->assertEquals('zizoo',$this->mapper->findAttribute('name','my-value'));
    }

    /** @test */
    public function find_attribute_returns_null()
    {
        $xml = '<xml id="33"><content att="something"><first><second><edu><e><d><u><edu></edu></u></d></e></edu><my-value name="zizoo"></my-value></second></first></content></xml>';

        $this->mapper->loadXML($xml);

        $this->assertNull($this->mapper->findAttribute('name','no-exists'));
    }

    /** @test */
    public function find_attribute_by_name()
    {
        $xml = '<xml id="33"><content att="something"><first><second><edu><e><d><u><edu></edu></u></d></e></edu><my-value name="zizoo"></my-value></second></first></content></xml>';

        $this->mapper->loadXML($xml);

        $this->assertEquals('zizoo',$this->mapper->findAttribute('name'));
    }

    /** @test */
    public function find_attribute_by_where()
    {
        $xml = '<xml id="33"><content att="something"><first><second><edu><e><d><u><edu></edu></u></d></e></edu><my-value name="zizoo" id="1" dev="edu"></my-value></second></first></content></xml>';

        $this->mapper->loadXML($xml);

        $this->assertEquals('zizoo',$this->mapper->findAttributeWhere('name',['id'=>1,'dev'=> 'edu',['name','!=','john']]));
    }

    /** @test */
    public function find_attribute_by_where_with_multiple_extras()
    {
        $xml = '<xml id="33"><content att="something"><first><second><extras><extra name="wrong" id="2" dev="other"></extra><extra name="zizoo" id="1" dev="edu"></extra></extras></second></first></content></xml>';

        $this->mapper->loadXML($xml);

        $this->assertEquals('zizoo',$this->mapper->findAttributeWhere('name',[['id','!=',2],'dev'=> 'edu']));
    }

    /** @test */
    public function find_attributes_without_tag()
    {
        $xml = '<xml id="33"><content att="something"><first><second><extras><extra name="zizoo" id="1" dev="edu"></extra></extras></second></first></content></xml>';

        $this->mapper->loadXML($xml);
        $this->assertInstanceOf(\stdClass::class,$this->mapper->findAttributes(['name','dev']));
        $this->assertEquals('zizoo',$this->mapper->findAttributes(['name','dev'])->name);
        $this->assertEquals('edu',$this->mapper->findAttributes(['name','dev'])->dev);
    }

    /** @test */
    public function find_attributes_with_tag()
    {
        $xml = '<xml id="33"><content att="something"><first><second><extras><extra name="f" id="2" dev="a"></extra><extra name="zizoo" id="1" dev="edu"></extra></extras></second></first></content></xml>';

        $this->mapper->loadXML($xml);
        $this->assertInstanceOf(\stdClass::class,$this->mapper->findAttributesWhere(['name','dev'],['id'=>'1']));
        $this->assertEquals('zizoo',$this->mapper->findAttributesWhere(['name','dev'],['id'=>'1'])->name);
        $this->assertEquals('edu',$this->mapper->findAttributesWhere(['name','dev'],['id'=>'1'])->dev);
    }

    /** @test */
    public function get_all_attr_for_a_node()
    {
        $xml = '<xml id="33"><content att="something"><first><second><extras><extra name="f" id="2" dev="a"></extra><extra name="zizoo" id="1" dev="edu"></extra></extras></second></first></content></xml>';

        $this->mapper->loadXML($xml);

        $result = $this->mapper->findAllAttributesOf('extra');

        $this->assertCount(2,$result);
        $this->assertEquals('zizoo',$result[1]->name);
    }

    /** @test */
    public function get_all_attr_by_condition()
    {
        $xml = '<xml id="33"><content att="something"><first><second><extras><extra name="f" id="2" dev="a"></extra><extra name="zizoo" id="1" dev="edu"></extra></extras></second></first></content></xml>';

        $this->mapper->loadXML($xml);

        $result = $this->mapper->findAllAttributesOfWhere('extra',['id'=>1]);

        $this->assertCount(1,$result);
        $this->assertEquals('zizoo',$result[0]->name);
    }

    /** @test */
    public function get_an_element_by_tag()
    {
        $xml = $this->loadXML();

        $this->mapper->loadXML($xml);

        $result = $this->mapper->getElement('book2');

        $this->assertInstanceOf(\Edujugon\XMLMapper\XMLMapper::class,$result);
        $this->assertEquals('WEB',$result->getAttribute('category'));
    }

    /** @test */
    public function returns_null_when_wrong_tag_name()
    {
        $xml = $this->loadXML();

        $this->mapper->loadXML($xml);

        $result = $this->mapper->getElement('books');

        $this->assertNull($result);
    }

    /** @test */
    public function get_all_elements_by_tag()
    {
        $xml = $this->loadXML();

        $this->mapper->loadXML($xml);

        $result = $this->mapper->getElements('book');

        $this->assertInternalType('array',$result);
        $this->assertCount(4,$result);
        foreach ($result as $item) {
            $this->assertInstanceOf(\Edujugon\XMLMapper\XMLMapper::class,$item);
        }
    }

    private function loadXML()
    {
        return '<?xml version="1.0" encoding="utf-8"?>
        <bookstore>
          <book category="COOKING">
            <title lang="en">Everyday Italian</title>
            <author>Giada De Laurentiis</author>
            <year>2005</year>
            <price>30.00</price>
          </book>
          <book category="CHILDREN">
            <title lang="en">Harry Potter</title>
            <author>J K. Rowling</author>
            <year>2005</year>
            <price>29.99</price>
          </book>
          <section>
              <book category="WEB">
                <title lang="en-us">XQuery Kick Start</title>
                <author>James McGovern</author>
                <year>2003</year>
                <price>49.99</price>
              </book>
              <book category="WEB">
                <title lang="en-us">Learning XML</title>
                <author>Erik T. Ray</author>
                <year>2003</year>
                <price>39.95</price>
              </book>
          </section>
          <subsection>
              <book2 category="WEB">
                <title lang="en-us">XQuery Kick Start</title>
                <author>James McGovern</author>
                <year>2003</year>
                <price>49.99</price>
              </book2>
              <book2 category="WEB">
                <title lang="en-us">Learning XML</title>
                <author>Erik T. Ray</author>
                <year>2003</year>
                <price>39.95</price>
              </book2>
          </subsection>
        </bookstore>';
    }
}