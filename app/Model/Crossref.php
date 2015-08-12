<?php

App::uses('AppModel', 'Model');
App::uses('ClassRegistry', 'Utility');

/**
 * Class Crossref
 */
class Crossref extends AppModel {

	public $useTable=false;

    /**
     * Get DOI via CrossRef OpenURL API
     * @param $citation
     * @return mixed
     */
    public function openurl($citation)
	{
		// Do DOI lookup via Crossref (get article title and full names of authors)
		$HttpSocket = new HttpSocket();
		$get=['pid'=>'schalk@unf.edu','noredirect'=>'true'];
		if($citation['authors']!="[]") {
			$authors=json_decode($citation['authors'],true);
			$get['aulast']=$authors[0]['lastname'];
		} else {
			$get['aulast']="";
		}
		if(isset($citation['journal']))		{ $get['title']=$citation['journal']; }
		if(isset($citation['volume']))		{ $get['volume']=$citation['volume']; }
		if(isset($citation['issue']))		{ $get['issue']=$citation['issue']; }
		if(isset($citation['startpage']))	{ $get['spage']=$citation['startpage']; }
		if(isset($citation['year']))		{ $get['date']=$citation['year']; }
		$response=$HttpSocket->get("http://www.crossref.org/openurl",$get);
		$xml=simplexml_load_string($response['body']);
		$meta=json_decode(json_encode($xml->query_result->body->query),true);

		if($meta['@attributes']['status']=="resolved") {
			if(isset($meta['doi'])) { $citation['doi']=$meta['doi']; }
			if(isset($meta['journal_title'])) { $citation['journal']=$meta['journal_title']; }
			if(isset($meta['article_title'])) { $citation['title']=$meta['article_title']; }
			if(isset($meta['last_page'])) { $citation['endpage']=$meta['last_page']; }
			if(isset($meta['issue'])&&$meta['issue']!='0') { $citation['issue']=$meta['issue']; }
			if(isset($meta['contributors']['contributor'])) {
				$authors=[]; // Deletes out authors obtained from citation
				$cons=$meta['contributors']['contributor'];
				(!isset($cons[0])) ? $aus=[$cons] : $aus=$cons;
				foreach($aus as $au) {
					if(isset($au['given_name'])):	$authors[]=['firstname'=>$au['given_name'],'lastname'=>$au['surname']];
					else:							$authors[]=['firstname'=>'','lastname'=>$au['surname']];
					endif;
				}
			}
			$citation['authors']=json_encode($authors);
			$citation['crossref']="yes";
		}
		return $citation;
	}

}