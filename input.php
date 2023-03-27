<?php

function f(array $set, string $value) {
	foreach ($set as $entry) {
		if (strcasecmp($entry, $value) === 0) {
			return true;
		}
	}
	return false;
}
