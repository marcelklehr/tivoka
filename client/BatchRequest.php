<?php
/**
 * @package Tivoka
 * @author Marcel Klehr <mklehr@gmx.net>
 * @copyright (c) 2011, Marcel Klehr
 */
/**
 * A batch request
 * @package Tivoka
 */
class Tivoka_BatchRequest extends Tivoka_Request
{
	/**
	 * Constructs a new JSON-RPC batch request
	 * All values of type other than Tivoka_Request will be ignored
	 * @param array $batch A list of requests to include, each a Tivoka_Request
	 * @see Tivoka_Client::send()
	 */
	public function __construct(array $batch)
	{
		if(Tivoka::$version == Tivoka::VER_1_0) throw new Tivoka_exception('Batch requests are not supported by JSON-RPC v1.0', Tivoka::ERR_SPEC_INCOMPATIBLE);
		$this->id = array();
	
		//prepare requests...
		foreach($batch as $request)
		{
			if(!($request instanceof Tivoka_Request) && !($request instanceof Tivoka_Notification))
				continue;
			
			//request...
			if($request instanceof Tivoka_Request)
			{
				if(in_array($request->id,$this->id,true)) continue;
				$this->id[$request->id] = $request;
			}
			
			$this->data[] = $request->data;
		}
		
		$this->response = new Tivoka_BatchResponse($this->id);
	}
}
?>