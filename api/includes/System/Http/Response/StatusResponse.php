<?php

namespace System\Http\Response;

use RuntimeException;

class StatusResponse extends JsonResponse {
	
	/**
	 * @param mixed $payload
	 * @return void
	 */
	public function setPayload(mixed $payload): void {
		if (!is_string($payload))
			throw new RuntimeException("Payload must be a string");
		$this->value = ['success'=>true,'message'=>$payload];
	}
	
	public function append(mixed $payload): bool {
		if (is_string($payload)) {
			$this->value['message'] .= $payload;
			return true;
		}
		return false;
	}
	
	public function setStatus(bool $status): void {
		$this->value['success'] = $status;
	}
	
}
