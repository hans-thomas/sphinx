<?php


	namespace Hans\Sphinx\Exceptions;


	use Exception;
	use Illuminate\Http\JsonResponse;
	use Throwable;

	class SphinxException extends Exception {
		private int $errorCode;

		public function __construct( string $message, int $errorCode, int $responseCode = 500, Throwable $previous = null ) {
			parent::__construct( $message, $responseCode, $previous );
			$this->errorCode = $errorCode;
		}

		/**
		 * Render the exception into an HTTP response.
		 *
		 *
		 * @return JsonResponse
		 */
		public function render(): JsonResponse {
			return new JsonResponse( [
				'code'   => $this->getErrorCode(),
				'detail' => $this->getMessage(),
				'title'  => "Unexpected error!"
			], $this->getCode() );
		}

		public function getErrorCode(): int {
			return $this->errorCode;
		}
	}
