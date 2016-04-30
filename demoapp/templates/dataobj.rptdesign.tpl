<?xml version="1.0" encoding="UTF-8"?>
<!-- Written by Eclipse BIRT 2.0 -->
<report xmlns="http://www.eclipse.org/birt/2005/design" version="3.2.6" id="1">
    <property name="createdBy">Eclipse BIRT Designer Version 2.1.2.v20070205-1728 Build &lt;20070205-1728></property>
    <property name="units">in</property>
    <property name="comments">Copyright (c) 2006 &lt;&lt;Your Company Name here>></property>
    <data-sources>
        <oda-data-source extensionID="org.eclipse.datatools.connectivity.oda.flatfile" name="Files" id="7">
            <property name="HOME">{$rpt_data_dir}</property>
            <property name="CHARSET">UTF-8</property>
            <property name="INCLTYPELINE">NO</property>
        </oda-data-source>
    </data-sources>
    <data-sets>
        <oda-data-set extensionID="org.eclipse.datatools.connectivity.oda.flatfile.dataSet" name="DataObject" id="8">
            <property name="dataSource">Files</property>
            <list-property name="resultSet">
                 {foreach item=fld from=$rpt_fields}<structure>
                    <property name="name">{$fld.name}</property>
                    <property name="nativeName">{$fld.name}</property>
                    <property name="dataType">{$fld.type}</property>
                    <property name="nativeDataType">12</property>
                 </structure>
               {/foreach}
            </list-property>
            <property name="queryText">
               {strip}
               select 
               {foreach item=fld from=$rpt_fields name=rptfields} 
                  {if $smarty.foreach.rptfields.last} {$fld.name} {else} {$fld.name},{/if} 
               {/foreach} 
               from {$rpt_csv_file} 
               {/strip}
            </property>
        </oda-data-set>
    </data-sets>
    <page-setup>
        <simple-master-page name="Simple MasterPage" id="2">
            <page-footer>
                <text id="3">
                    <property name="contentType">html</property>
                    <text-property name="content"><![CDATA[<value-of>new Date()</value-of>]]></text-property>
                </text>
            </page-footer>
        </simple-master-page>
    </page-setup>
    <body>
        <grid id="9">
            <property name="width">100%</property>
            <column id="10"/>
            <row id="11">
                <cell id="12">
                    <text id="38">
                        <property name="contentType">html</property>
                        <text-property name="content"><![CDATA[<CENTER>
<B>{$rpt_title}</B>
</CENTER>]]></text-property>
                    </text>
                </cell>
            </row>
            <row id="13">
                <cell id="14">
                    <table id="15">
                        <property name="width">100%</property>
                        <property name="dataSet">DataObject</property>
                        <list-property name="boundDataColumns">
                            {foreach item=fld from=$rpt_fields}<structure>
                                <property name="name">{$fld.name}</property>
                                <expression name="expression">dataSetRow["{$fld.name}"]</expression>
                                <property name="dataType">{$fld.type}</property>
                              </structure>
                            {/foreach}
                        </list-property>
                        <column id="25"/>
                        <column id="26"/>
                        <header>
                            <row id="16">
                                {foreach item=fld from=$rpt_fields}<cell>
                                       <label>
                                           <text-property name="text">{$fld.name}</text-property>
                                       </label>
                                   </cell>
                                {/foreach}
                            </row>
                        </header>
                        <detail>
                            <row id="19">
                                {foreach item=fld from=$rpt_fields}<cell>
                                       <data>
                                        <property name="resultSetColumn">{$fld.name}</property>
                                       </data>
                                   </cell>
                                {/foreach}
                            </row>
                        </detail>
                        <footer>
                            <row id="22">
                                <cell id="23"/>
                                <cell id="24"/>
                            </row>
                        </footer>
                    </table>
                </cell>
            </row>
        </grid>
    </body>
</report>
