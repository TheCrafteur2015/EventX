<?php

namespace App\Controller;

use System\Controller;
use System\Http\Response\Response;

class DeleteController extends Controller {

	/**
	 * This method removes all temp files from the dedicated temp directory.
	 */
	public function removeTempFiles(): Response {
		$dir = DOCUMENT_ROOT.TMP_PATH;
		$files = scandir($dir);
		foreach ($files as $file)
			if (preg_match('/^[.]{1,2}$/', $file) === 0)
				unlink($dir.$file);
		return $this->successful("Successfully removed temp files!");
	}

	function updateTokens(): void {
		$this->db->query("DELETE FROM authtoken WHERE duedate > datecreated AND duedate < NOW();");
	}

}
