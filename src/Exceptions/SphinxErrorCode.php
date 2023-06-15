<?php

	namespace Hans\Sphinx\Exceptions;

	class SphinxErrorCode {
		public const TOKEN_EXPIRED = 1001;
		public const DECODE_FAILED = 1003;
		public const ROLE_NOT_FOUND = 1004;
		public const ROLE_VERSION_NOT_FOUND = 1005;
		public const TOKEN_IS_OUT_OF_DATE = 1006;
		public const SESSION_NOT_FOUND = 1007;
		public const USERS_VERSION_NOT_FOUND = 1008;
		public const TOKEN_MISMATCH = 1010;
		public const CAPTURE_SESSION_FAILED = 1011;
		public const FAILED_TO_SET_CLAIM = 1011;
		public const FAILED_TO_SET_HEADER = 1011;
	}
