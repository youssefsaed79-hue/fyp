<?php
/**
 * InternFlow - Root Directory Redirection
 * Forwards requests from http://localhost/internflow directly to http://localhost/internflow/public/
 */
header('Location: public/');
exit;
