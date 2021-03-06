<?php

/*
 * +----------------------------------------------------------------------+
 * | PHP Version 4                                                        |
 * +----------------------------------------------------------------------+
 * | Copyright (c) 2002-2005 Heinrich Stamerjohanns                       |
 * |                                                                      |
 * | getrecord.php -- Utilities for the OAI Data Provider                 |
 * |                                                                      |
 * | This is free software; you can redistribute it and/or modify it under|
 * | the terms of the GNU General Public License as published by the      |
 * | Free Software Foundation; either version 2 of the License, or (at    |
 * | your option) any later version.                                      |
 * | This software is distributed in the hope that it will be useful, but |
 * | WITHOUT  ANY WARRANTY; without even the implied warranty of          |
 * | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the         |
 * | GNU General Public License for more details.                         |
 * | You should have received a copy of the GNU General Public License    |
 * | along with  software; if not, write to the Free Software Foundation, |
 * | Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 USA         |
 * |                                                                      |
 * +----------------------------------------------------------------------+
 * | Derived from work by U. M�ller, HUB Berlin, 2002                     |
 * |                                                                      |
 * | Written by Heinrich Stamerjohanns, May 2002                          |
 * |            stamer@uni-oldenburg.de                                   |
 * +----------------------------------------------------------------------+
 */
//
// $Id: getrecord.php,v 1.02 2003/04/08 14:22:07 stamer Exp $
//
// parse and check arguments
foreach ($args as $key => $val) {

    switch ($key) {
        case 'identifier':
            $identifier = $val;
            if (!is_valid_uri($identifier)) {
                $errors .= oai_error('badArgument', $key, $val);
            }
            break;
    }
}

if (!isset($args['identifier'])) {
    $errors .= oai_error('missingArgument', 'identifier');
}
//if (!isset($args['metadataPrefix'])) {
//    $errors .= oai_error('missingArgument', 'metadataPrefix');
//}
///////explode identifier for use/////////////
$identifier = explode('scorm:' . $repositoryIdentifier . ':', $identifier);
$identifier = $identifier[1];
$identifier = onlyNumbers($identifier);


/* $XMLHEADER =
  '<?xml version="1.0" encoding="UTF-8"?>
  <OAI-PMH xmlns="http://www.openarchives.org/OAI/2.0/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"  xmlns:lom="http://ltsc.ieee.org/xsd/LOM" xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/ http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd">' . "\n";
 */
$XMLHEADER = '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . '<manifest>';



$xmlheader = $XMLHEADER;

////////////query if exist metadata record in the db!!!!//////////////////////////////
$sqlmetadatarecord = "select * from metadata_record where object_id=" . $identifier . " and object_type='exhibit' and validate=1";
//echo $sqlmetadatarecord; //break;
$exec2 = $db->query($sqlmetadatarecord);
$metadatarecord = $exec2->fetch();

if (!isset($metadatarecord['id'])) {
    $errors .= oai_error('idDoesNotExist', NULL, $identifier);
}

if (empty($errors)) { //if no errors
    $sqlomekaitem = "select * from omeka_exhibits where id=" . $identifier . "";
//echo $sqlmetadatarecord; //break;
    $execomekaitem = $db->query($sqlomekaitem);
    $omekaitem = $execomekaitem->fetch();




    $sqlmetadatarecordvalue = "select * from metadata_element_value where record_id=" . $metadatarecord['id'] . " ORDER BY element_hierarchy ASC";
    $exec = $db->query($sqlmetadatarecordvalue);
    $metadatarecordvalue_res = $exec->fetchAll();
//echo $sqlmetadatarecordvalue; break;
//$metadatarecordvalue_res=mysql_query($sqlmetadatarecordvalue);
//$metadatarecordvalue=mysql_fetch_array($metadatarecordvalue_res);



    $output .= '<metadata>';
    $output .= '<lom>';

//query for creating general elements pelement=0		 
    $sql3 = "SELECT c.*,b.machine_name,b.id as elm_id2 FROM  metadata_element b  LEFT JOIN metadata_element_hierarchy c 
			ON c.element_id = b.id WHERE c.pelement_id=0 and c.is_visible=1  ORDER BY (case WHEN c.sequence IS NULL THEN '9999' ELSE c.sequence END) ASC;";
    $exec3 = $db->query($sql3);
    $datageneral3 = $exec3->fetchAll();


/////////////////////////




    foreach ($datageneral3 as $datageneral3) {

        $output2 = '';
        $sql4 = "SELECT c.*,b.machine_name,b.id as elm_id FROM  metadata_element b  LEFT JOIN metadata_element_hierarchy c 
			ON c.element_id = b.id  WHERE c.pelement_id=" . $datageneral3['elm_id2'] . " and c.is_visible=1 ORDER BY (case WHEN c.sequence IS NULL THEN '9999' ELSE c.sequence END) ASC;";
        //echo $sql4;break;
        $exec4 = $db->query($sql4);
        $datageneral4 = $exec4->fetchAll();


        if ($datageneral3['machine_name'] == 'rights') { ///////if RIGHTS
            $output2.= preview_elements($datageneral4, NULL, $metadatarecord, $datageneral3, '');
        } elseif ($datageneral3['machine_name'] == 'classification') { ///////if CLASSIFICATION
            $output.= preview_elements($datageneral4, NULL, $metadatarecord, $datageneral3, '');
        } elseif ($datageneral3['machine_name'] == 'relation') { ///////if RELATION
            $output2.= preview_elements($datageneral4, NULL, $metadatarecord, $datageneral3, '');
        } else { ///the rest parent elements///////////////////////////////
            foreach ($datageneral4 as $datageneral4) {



                $sql5 = "SELECT * FROM  metadata_element_value WHERE record_id=" . $metadatarecord['id'] . " and element_hierarchy=" . $datageneral4['id'] . " ORDER BY multi ASC;";
                //echo $sql4."<br>";
                $exec5 = $db->query($sql5);
                $datageneral5 = $exec5->fetchAll();
                $count_results = count($datageneral5);

                if ($count_results > 0) {

                    if ($datageneral3['machine_name'] == 'general') { ///////if GENERAL
                        $output2.= preview_elements($datageneral4, $datageneral5, $metadatarecord, $datageneral3, '');
                    } elseif ($datageneral3['machine_name'] == 'educational') { ///////if EDUCATIONAL
                        $output2.= preview_elements($datageneral4, $datageneral5, $metadatarecord, $datageneral3, '');
                    } elseif ($datageneral3['machine_name'] == 'technical') { ///////if TECHNICAL
                        $output2.= preview_elements($datageneral4, $datageneral5, $metadatarecord, $datageneral3, '');
                    } elseif ($datageneral3['machine_name'] == 'lifeCycle') { ///////if LIFECYCLE
                        $output2.= preview_elements($datageneral4, $datageneral5, $metadatarecord, $datageneral3, '');
                    } elseif ($datageneral3['machine_name'] == 'metaMetadata') { ///////if META-METADATA
                        $output2.= preview_elements($datageneral4, $datageneral5, $metadatarecord, $datageneral3, '');
                    } elseif ($datageneral3['machine_name'] == 'annotation') { ///////if ANNOTATION
                        $output2.= preview_elements($datageneral4, $datageneral5, $metadatarecord, $datageneral3, '');
                    } else {
                        $output2.= preview_elements_from_datatype($datageneral4, $datageneral5, $metadatarecord, NULL, '');
                    }
                }//if count_results
            }//datageneral4
        } ///the rest parent elements///////////////////////////////	
        ////////////////echo the result of all parent element if exist
        if (strlen($output2) > 0) {

            $output.= '<' . $datageneral3['machine_name'] . '>';
            $output.= $output2;
            $output.= '</' . $datageneral3['machine_name'] . '>' . "\n";
        }
    }//datageneral3



    $output .= '</lom>' . "\n";
    $output .= '<omeka>';
    $output .= '<dateModified >';
    $output.= $metadatarecord['date_modified'];
    $output .= '</dateModified >' . "\n";
    $output .= '</omeka>' . "\n";
    $output .= '</metadata>' . "\n";


    $sqlehibitsections = "select * from omeka_sections where exhibit_id=" . $omekaitem['id'] . " ";
//echo $sqlmetadatarecord; //break;
    $ehibitsections = $db->query($sqlehibitsections);
    $sections = $ehibitsections->fetchAll();


    $output .= '<organizations default="ORG-Pathway">';
    $output .= '<organization identifier="ORG-' . $omekaitem['id'] . '" structure="hierarchical">';
    $output .= '<title>'; ///for pathway
    $output .= '<![CDATA[' . $omekaitem['title'] . ']]>'; ///for pathway
    $output .= '</title>' . "\n";
    foreach ($sections as $sections2) {

        $sqlsectionpages = "select * from omeka_section_pages where section_id=" . $sections2['id'] . " ";
//echo $sqlmetadatarecord; //break;
        $sectionpages = $db->query($sqlsectionpages);
        $pages = $sectionpages->fetchAll();

        $output .= '<item identifier="ITEM-' . $sections2['id'] . '" isvisible="true">';
        $output .= '<title>'; //for section
        $output .= $sections2['title']; ///for section
        $output .= '</title>' . "\n";
        foreach ($pages as $pages) {
            $output .= '<item identifier="ITEM-' . $sections2['id'] . '-' . $pages['id'] . '" identifierref="RES-' . $sections2['id'] . '-' . $pages['id'] . '" isvisible="true">';
            $output .= '<title>'; //for page
            $output .= $pages['title']; ///for page
            $output .= '</title>' . "\n";
            $output .= '</item>' . "\n";
        }

        $output .= '</item>' . "\n";
    }

    $output .= '</organization>' . "\n";
    $output .= '</organizations>' . "\n";


    $output .= '<resources>';

    foreach ($sections as $sections2) {

        $sqlsectionpages = "select * from omeka_section_pages where section_id=" . $sections2['id'] . " ";
//echo $sqlmetadatarecord; //break;
        $sectionpages = $db->query($sqlsectionpages);
        $pages = $sectionpages->fetchAll();

        foreach ($pages as $pages) {
            $output .= '<resource identifier="RES-' . $sections2['id'] . '-' . $pages['id'] . '" >';
            $output .= '<metadata>';
            $output .= '<lom>';
            $output .= '<general>';
            $output .= '<title>';
            $output .= '<string>';
            $output .= $pages['title']; ///for page
            $output .= '</string>' . "\n";
            $output .= '</title>' . "\n";
            
            
            
            $sqlpagestext = "select * from omeka_items_section_pages where page_id=" . $pages['id'] . " ORDER BY `order` ASC";
//echo $sqlmetadatarecord; //break;
            $pagestextr = $db->query($sqlpagestext);
            $pagestext2 = $pagestextr->fetchAll();
            foreach ($pagestext2 as $pagestext) {
                ////replace strange space ascii character////////////////
                $pagestexttext = trim ($pagestext['text']);
                $pagestexttext = trim($pagestexttext,chr(0xC2).chr(0xA0).chr(0xb)); 
                $pagestexttext = str_replace("", " ", $pagestext['text']);
                if(strlen($pagestexttext)>0){
                $output .= '<description order="'.$pagestext['order'].'">';
                $output .= '<string>';
                $output .= '<![CDATA[';
                $output .= trim ($pagestexttext) . "";
                $output .= ']]>';
                $output .= '</string>' . "\n";
                $output .= '</description>' . "\n";
                }
            }
            
            
            
            $output .= '</general>' . "\n";
            $output .= '</lom>' . "\n";
            $output .= '</metadata>' . "\n";


            foreach ($pagestext2 as $pagestext3) {
                
                if ($pagestext3['item_id'] > 0) {


                    $sqlmetadatarecord = "select * from metadata_record where object_id=" . $pagestext3['item_id'] . " and object_type='item' and validate=1";
//echo $sqlmetadatarecord; //break;
                    $exec2 = $db->query($sqlmetadatarecord);
                    $metadatarecord = $exec2->fetch();

                    $sqlomekaitem = "select * from omeka_items where id=" . $pagestext3['item_id'] . "";
//echo $sqlmetadatarecord; //break;
                    $execomekaitem = $db->query($sqlomekaitem);
                    $omekaitem = $execomekaitem->fetch();


                    if ($metadatarecord['id'] > 0) {
                        $output .= '<file inText="yes" order="'.$pagestext3['order'].'">';
                        $sqlmetadatarecordvalue = "select * from metadata_element_value where record_id=" . $metadatarecord['id'] . " ORDER BY element_hierarchy ASC";
                        $exec = $db->query($sqlmetadatarecordvalue);
                        $metadatarecordvalue_res = $exec->fetchAll();
//echo $sqlmetadatarecordvalue; break;
//$metadatarecordvalue_res=mysql_query($sqlmetadatarecordvalue);
//$metadatarecordvalue=mysql_fetch_array($metadatarecordvalue_res);



                        $output .= '<metadata>';
                        $output .= '<lom>';

//query for creating general elements pelement=0		 
                        $sql3 = "SELECT c.*,b.machine_name,b.id as elm_id2 FROM  metadata_element b  LEFT JOIN metadata_element_hierarchy c 
			ON c.element_id = b.id WHERE c.pelement_id=0 and c.is_visible=1  ORDER BY (case WHEN c.sequence IS NULL THEN '9999' ELSE c.sequence END) ASC;";
                        $exec3 = $db->query($sql3);
                        $datageneral3 = $exec3->fetchAll();


/////////////////////////




                        foreach ($datageneral3 as $datageneral3) {

                            $output2 = '';
                            $sql4 = "SELECT c.*,b.machine_name,b.id as elm_id FROM  metadata_element b  LEFT JOIN metadata_element_hierarchy c 
			ON c.element_id = b.id  WHERE c.pelement_id=" . $datageneral3['elm_id2'] . " and c.is_visible=1 ORDER BY (case WHEN c.sequence IS NULL THEN '9999' ELSE c.sequence END) ASC;";
                            //echo $sql4;break;
                            $exec4 = $db->query($sql4);
                            $datageneral4 = $exec4->fetchAll();


                            if ($datageneral3['machine_name'] == 'rights') { ///////if RIGHTS
                                $output2.= preview_elements($datageneral4, NULL, $metadatarecord, $datageneral3, '');
                            } elseif ($datageneral3['machine_name'] == 'classification') { ///////if CLASSIFICATION
                                $output.= preview_elements($datageneral4, NULL, $metadatarecord, $datageneral3, '');
                            } elseif ($datageneral3['machine_name'] == 'relation') { ///////if RELATION
                                $output2.= preview_elements($datageneral4, NULL, $metadatarecord, $datageneral3, '');
                            } else { ///the rest parent elements///////////////////////////////
                                foreach ($datageneral4 as $datageneral4) {



                                    $sql5 = "SELECT * FROM  metadata_element_value WHERE record_id=" . $metadatarecord['id'] . " and element_hierarchy=" . $datageneral4['id'] . " ORDER BY multi ASC;";
                                    //echo $sql4."<br>";
                                    $exec5 = $db->query($sql5);
                                    $datageneral5 = $exec5->fetchAll();
                                    $count_results = count($datageneral5);

                                    if ($count_results > 0) {

                                        if ($datageneral3['machine_name'] == 'general') { ///////if GENERAL
                                            $output2.= preview_elements($datageneral4, $datageneral5, $metadatarecord, $datageneral3, '');
                                        } elseif ($datageneral3['machine_name'] == 'educational') { ///////if EDUCATIONAL
                                            $output2.= preview_elements($datageneral4, $datageneral5, $metadatarecord, $datageneral3, '');
                                        } elseif ($datageneral3['machine_name'] == 'technical') { ///////if TECHNICAL
                                            $output2.= preview_elements($datageneral4, $datageneral5, $metadatarecord, $datageneral3, '');
                                        } elseif ($datageneral3['machine_name'] == 'lifeCycle') { ///////if LIFECYCLE
                                            $output2.= preview_elements($datageneral4, $datageneral5, $metadatarecord, $datageneral3, '');
                                        } elseif ($datageneral3['machine_name'] == 'metaMetadata') { ///////if META-METADATA
                                            $output2.= preview_elements($datageneral4, $datageneral5, $metadatarecord, $datageneral3, '');
                                        } elseif ($datageneral3['machine_name'] == 'annotation') { ///////if ANNOTATION
                                            $output2.= preview_elements($datageneral4, $datageneral5, $metadatarecord, $datageneral3, '');
                                        } else {
                                            $output2.= preview_elements_from_datatype($datageneral4, $datageneral5, $metadatarecord, NULL, '');
                                        }
                                    }//if count_results
                                }//datageneral4
                            } ///the rest parent elements///////////////////////////////	
                            ////////////////echo the result of all parent element if exist
                            if (strlen($output2) > 0) {

                                $output.= '<' . $datageneral3['machine_name'] . '>';
                                $output.= $output2;
                                $output.= '</' . $datageneral3['machine_name'] . '>' . "\n";
                            }
                        }//datageneral3



                        $output .= '</lom>' . "\n";
                        $output .= '</metadata>' . "\n";
                        
                        $sqlomekaitemfile = "select * from omeka_files where item_id=" . $omekaitem['id'] . "";
//echo $sqlmetadatarecord; //break;
                    $execomekaitemfile = $db->query($sqlomekaitemfile);
                    $omekaitemfile = $execomekaitemfile->fetch();
                        if(strlen($omekaitemfile['archive_filename'])>4){
                            $output .= '<thumbs>';
                            $output .= '<full>';
                            $output .= 'http://' . $_SERVER['SERVER_NAME'] . '' . uri('archive/files/'.$omekaitemfile['archive_filename'].'');
                            $output .= '</full>' . "\n";
                            $output .= '<thumbnails>';
                            $output .= 'http://' . $_SERVER['SERVER_NAME'] . '' . uri('archive/thumbnails/'.$omekaitemfile['archive_filename'].'');
                            $output .= '</thumbnails>' . "\n";
                            $output .= '<square_thumbnails>';
                            $output .= 'http://' . $_SERVER['SERVER_NAME'] . '' . uri('archive/square_thumbnails/'.$omekaitemfile['archive_filename'].'');
                            $output .= '</square_thumbnails>' . "\n";
                            $output .= '</thumbs>' . "\n";
                        }
                    $sqlomekaitemfile = "select * from omeka_files where item_id=" . $omekaitem['id'] . "";
//echo $sqlmetadatarecord; //break;
                    $execomekaitemfile = $db->query($sqlomekaitemfile);
                    $omekaitemfile = $execomekaitemfile->fetch();
                        if(strlen($omekaitemfile['archive_filename'])>4){
                            $output .= '<thumbs>';
                            $output .= '<full>';
                            $output .= 'http://' . $_SERVER['SERVER_NAME'] . '' . uri('archive/files/'.$omekaitemfile['archive_filename'].'');
                            $output .= '</full>' . "\n";
                            $output .= '<thumbnails>';
                            $output .= 'http://' . $_SERVER['SERVER_NAME'] . '' . uri('archive/thumbnails/'.$omekaitemfile['archive_filename'].'');
                            $output .= '</thumbnails>' . "\n";
                            $output .= '<square_thumbnails>';
                            $output .= 'http://' . $_SERVER['SERVER_NAME'] . '' . uri('archive/square_thumbnails/'.$omekaitemfile['archive_filename'].'');
                            $output .= '</square_thumbnails>' . "\n";
                            $output .= '</thumbs>' . "\n";
                        }

                        $output .= '</file>' . "\n";
                    }///if($metadatarecord['id']>0){
                }
            }

            $maxIdSQL = "select * from omeka_teasers where exhibit_id=" . $identifier . " and type!='europeana' and pg_id=" . $pages['id'] . " and sec_id=" . $sections2['id'];
//echo $maxIdSQL;break;
            $exec = $db->query($maxIdSQL);
            $result_multi = $exec->fetchAll();
            foreach ($result_multi as $result_multi) {
                if ($result_multi['item_id'] > 0) {

                    $sqlmetadatarecord = "select * from metadata_record where object_id=" . $result_multi['item_id'] . " and object_type='item' and validate=1";
//echo $sqlmetadatarecord; //break;
                    $exec2 = $db->query($sqlmetadatarecord);
                    $metadatarecord = $exec2->fetch();

                    $sqlomekaitem = "select * from omeka_items where id=" . $result_multi['item_id'] . "";
//echo $sqlmetadatarecord; //break;
                    $execomekaitem = $db->query($sqlomekaitem);
                    $omekaitem = $execomekaitem->fetch();


                    if ($metadatarecord['id'] > 0) {

                        $sqlmetadatarecordvalue = "select * from metadata_element_value where record_id=" . $metadatarecord['id'] . " ORDER BY element_hierarchy ASC";
                        $exec = $db->query($sqlmetadatarecordvalue);
                        $metadatarecordvalue_res = $exec->fetchAll();
//echo $sqlmetadatarecordvalue; break;
//$metadatarecordvalue_res=mysql_query($sqlmetadatarecordvalue);
//$metadatarecordvalue=mysql_fetch_array($metadatarecordvalue_res);


                        $output .= '<file>';
                        $output .= '<metadata>';
                        $output .= '<lom>';

//query for creating general elements pelement=0		 
                        $sql3 = "SELECT c.*,b.machine_name,b.id as elm_id2 FROM  metadata_element b  LEFT JOIN metadata_element_hierarchy c 
			ON c.element_id = b.id WHERE c.pelement_id=0 and c.is_visible=1  ORDER BY (case WHEN c.sequence IS NULL THEN '9999' ELSE c.sequence END) ASC;";
                        $exec3 = $db->query($sql3);
                        $datageneral3 = $exec3->fetchAll();


/////////////////////////




                        foreach ($datageneral3 as $datageneral3) {

                            $output2 = '';
                            $sql4 = "SELECT c.*,b.machine_name,b.id as elm_id FROM  metadata_element b  LEFT JOIN metadata_element_hierarchy c 
			ON c.element_id = b.id  WHERE c.pelement_id=" . $datageneral3['elm_id2'] . " and c.is_visible=1 ORDER BY (case WHEN c.sequence IS NULL THEN '9999' ELSE c.sequence END) ASC;";
                            //echo $sql4;break;
                            $exec4 = $db->query($sql4);
                            $datageneral4 = $exec4->fetchAll();


                            if ($datageneral3['machine_name'] == 'rights') { ///////if RIGHTS
                                $output2.= preview_elements($datageneral4, NULL, $metadatarecord, $datageneral3, '');
                            } elseif ($datageneral3['machine_name'] == 'classification') { ///////if CLASSIFICATION
                                $output.= preview_elements($datageneral4, NULL, $metadatarecord, $datageneral3, '');
                            } elseif ($datageneral3['machine_name'] == 'relation') { ///////if RELATION
                                $output2.= preview_elements($datageneral4, NULL, $metadatarecord, $datageneral3, '');
                            } else { ///the rest parent elements///////////////////////////////
                                foreach ($datageneral4 as $datageneral4) {



                                    $sql5 = "SELECT * FROM  metadata_element_value WHERE record_id=" . $metadatarecord['id'] . " and element_hierarchy=" . $datageneral4['id'] . " ORDER BY multi ASC;";
                                    //echo $sql4."<br>";
                                    $exec5 = $db->query($sql5);
                                    $datageneral5 = $exec5->fetchAll();
                                    $count_results = count($datageneral5);

                                    if ($count_results > 0) {

                                        if ($datageneral3['machine_name'] == 'general') { ///////if GENERAL
                                            $output2.= preview_elements($datageneral4, $datageneral5, $metadatarecord, $datageneral3, '');
                                        } elseif ($datageneral3['machine_name'] == 'educational') { ///////if EDUCATIONAL
                                            $output2.= preview_elements($datageneral4, $datageneral5, $metadatarecord, $datageneral3, '');
                                        } elseif ($datageneral3['machine_name'] == 'technical') { ///////if TECHNICAL
                                            $output2.= preview_elements($datageneral4, $datageneral5, $metadatarecord, $datageneral3, '');
                                        } elseif ($datageneral3['machine_name'] == 'lifeCycle') { ///////if LIFECYCLE
                                            $output2.= preview_elements($datageneral4, $datageneral5, $metadatarecord, $datageneral3, '');
                                        } elseif ($datageneral3['machine_name'] == 'metaMetadata') { ///////if META-METADATA
                                            $output2.= preview_elements($datageneral4, $datageneral5, $metadatarecord, $datageneral3, '');
                                        } elseif ($datageneral3['machine_name'] == 'annotation') { ///////if ANNOTATION
                                            $output2.= preview_elements($datageneral4, $datageneral5, $metadatarecord, $datageneral3, '');
                                        } else {
                                            $output2.= preview_elements_from_datatype($datageneral4, $datageneral5, $metadatarecord, NULL, '');
                                        }
                                    }//if count_results
                                }//datageneral4
                            } ///the rest parent elements///////////////////////////////	
                            ////////////////echo the result of all parent element if exist
                            if (strlen($output2) > 0) {

                                $output.= '<' . $datageneral3['machine_name'] . '>';
                                $output.= $output2;
                                $output.= '</' . $datageneral3['machine_name'] . '>' . "\n";
                            }
                        }//datageneral3



                        $output .= '</lom>' . "\n";
                        $output .= '</metadata>' . "\n";

                                                $sqlomekaitemfile = "select * from omeka_files where item_id=" . $omekaitem['id'] . "";
//echo $sqlmetadatarecord; //break;
                    $execomekaitemfile = $db->query($sqlomekaitemfile);
                    $omekaitemfile = $execomekaitemfile->fetch();
                        if(strlen($omekaitemfile['archive_filename'])>4){
                            $output .= '<thumbs>';
                            $output .= '<full>';
                            $output .= 'http://' . $_SERVER['SERVER_NAME'] . '' . uri('archive/files/'.$omekaitemfile['archive_filename'].'');
                            $output .= '</full>' . "\n";
                            $output .= '<thumbnails>';
                            $output .= 'http://' . $_SERVER['SERVER_NAME'] . '' . uri('archive/thumbnails/'.$omekaitemfile['archive_filename'].'');
                            $output .= '</thumbnails>' . "\n";
                            $output .= '<square_thumbnails>';
                            $output .= 'http://' . $_SERVER['SERVER_NAME'] . '' . uri('archive/square_thumbnails/'.$omekaitemfile['archive_filename'].'');
                            $output .= '</square_thumbnails>' . "\n";
                            $output .= '</thumbs>' . "\n";
                        }
                        $output .= '</file>' . "\n";
                    }
                }
            }




            $output .= '</resource>' . "\n";
        }
    }

    $output .= '</resources>' . "\n";
}//if no errors!!!
?>
