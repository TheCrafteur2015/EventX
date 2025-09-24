<?php

namespace System\Config;

enum ConnectionType: string {
	case WEB = "Web";
	case FETCH = "Fetch";
	case UNKNOWN = "Unknown";
}
