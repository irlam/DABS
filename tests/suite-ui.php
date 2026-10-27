<?php
declare(strict_types=1);
require dirname(__DIR__).'/includes/suite_ui.php';
$token=str_repeat('a',64);
$html=dabsSuiteNavigation(['user_name'=>'TEST User <script>bad()</script> $1','user_role'=>'viewer','csrf_token'=>$token],true,'TEST Company · TEST Project <img src=x>');
foreach ([
    str_contains($html,'https://suite.defecttracker.uk/'),
    str_contains($html,'method="post" action="/logout.php"'),
    str_contains($html,'value="'.$token.'"'),
    !str_contains($html,'href="logout.php"'),
    !str_contains($html,'<script>'),
    !str_contains($html,'<img src=x>'),
    str_contains($html,'&lt;script&gt;'),
    str_contains($html,'$1'),
    str_contains($html,'Viewer'),
    str_contains($html,'aria-label="Construction Suite"'),
    str_contains($html,'href="#dabs-workspace"'),
] as $check) if (!$check) throw new RuntimeException('Suite navigation regression.');
echo "Suite navigation: escaped identity/context, POST sign-out, CSRF and keyboard navigation pass.\n";
