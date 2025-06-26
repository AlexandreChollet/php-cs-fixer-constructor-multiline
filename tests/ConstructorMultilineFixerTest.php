<?php

namespace AlexandreChollet\PhpCsFixerConstructorMultiline\Tests;

use PHPUnit\Framework\TestCase;
use AlexandreChollet\PhpCsFixerConstructorMultiline\ConstructorMultilineFixer;
use PhpCsFixer\Tokenizer\Tokens;

class ConstructorMultilineFixerTest extends TestCase
{
    private ConstructorMultilineFixer $fixer;

    protected function setUp(): void
    {
        $this->fixer = new ConstructorMultilineFixer();
    }

    public function testSingleArgumentStaysSingleLine(): void
    {
        $input = '<?php
class Test {
    public function __construct($arg1) {
        // ...
    }
}';
        
        $expected = '<?php
class Test {
    public function __construct($arg1) {
        // ...
    }
}';

        $this->assertFixResult($input, $expected);
    }

    public function testMultipleArgumentsBecomeMultiline(): void
    {
        $input = '<?php
class Test {
    public function __construct($arg1, $arg2, $arg3) {
        // ...
    }
}';
        
        $expected = '<?php
class Test {
    public function __construct(
        $arg1,
        $arg2,
        $arg3
    ) {
        // ...
    }
}';
         
        $this->assertFixResult($input, $expected);
    }

    public function testMultipleArgumentsBecomeMultilineButWeirdlySpaced(): void
    {
        $input = '<?php
class Test {
    public function __construct($arg1,
     $arg2,             $arg3, $arg4) {
        // ...
    }
}';
        
        $expected = '<?php
class Test {
    public function __construct(
        $arg1,
        $arg2,
        $arg3,
        $arg4
    ) {
        // ...
    }
}';
         
        $this->assertFixResult($input, $expected);
    }

    public function testRealWorldCase(): void
    {
        $input = '<?php

namespace App\Command\Publication;

use App\Entity\Concession;
use App\Entity\FlatplanType;
use App\Filesystem\CracFilesystem;
use App\Repository\ConcessionRepository;
use App\Repository\FlatplanTypeRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class PublishCracPdfCommand extends Command
{
    protected static $defaultName = \'app:publish-crac-pdf\';

    public function __construct(private FlatplanTypeRepository $flatplanTypeRepository, private ConcessionRepository $concessionRepository, private CracFilesystem $cracFilesystem)
    {
        parent::__construct();
    }
}';
        
        $expected = '<?php

namespace App\Command\Publication;

use App\Entity\Concession;
use App\Entity\FlatplanType;
use App\Filesystem\CracFilesystem;
use App\Repository\ConcessionRepository;
use App\Repository\FlatplanTypeRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class PublishCracPdfCommand extends Command
{
    protected static $defaultName = \'app:publish-crac-pdf\';

    public function __construct(
        private FlatplanTypeRepository $flatplanTypeRepository,
        private ConcessionRepository $concessionRepository,
        private CracFilesystem $cracFilesystem
    )
    {
        parent::__construct();
    }
}';
         
        $this->assertFixResult($input, $expected);
    }

    public function testCompletelyFuckedUpConstructor(): void
    {
        $input = '<?php
class Test {
    public function __construct($arg1,
        $arg2,
            $arg3,
                $arg4,
                    $arg5) {
        // ...
    }
}';
        
        $expected = '<?php
class Test {
    public function __construct(
        $arg1,
        $arg2,
        $arg3,
        $arg4,
        $arg5
    ) {
        // ...
    }
}';
         
        $this->assertFixResult($input, $expected);
    }

    public function testConstructorWithRandomSpacesAndTabs(): void
    {
        $input = '<?php
class Test {
    public function __construct(	$arg1,	$arg2,	$arg3,	$arg4,	$arg5,	$arg6) {
        // ...
    }
}';
        
        $expected = '<?php
class Test {
    public function __construct(
        $arg1,
        $arg2,
        $arg3,
        $arg4,
        $arg5,
        $arg6
    ) {
        // ...
    }
}';
         
        $this->assertFixResult($input, $expected);
    }

    public function testConstructorWithMixedMessyFormatting(): void
    {
        $input = '<?php
class Test {
    public function __construct($arg1,
     $arg2,             $arg3,
        $arg4,
            $arg5, $arg6,
                $arg7) {
        // ...
    }
}';
        
        $expected = '<?php
class Test {
    public function __construct(
        $arg1,
        $arg2,
        $arg3,
        $arg4,
        $arg5,
        $arg6,
        $arg7
    ) {
        // ...
    }
}';
         
        $this->assertFixResult($input, $expected);
    }

    public function testConstructorWithCommentsInTheMiddle(): void
    {
        $input = '<?php
class Test {
    public function __construct($arg1, // first arg
     $arg2,             $arg3, // third arg
        $arg4, // fourth arg
            $arg5) { // last arg
        // ...
    }
}';
        
        $expected = '<?php
class Test {
    public function __construct(
        $arg1, // first arg
        $arg2,
        $arg3, // third arg
        $arg4, // fourth arg
        $arg5
    ) { // last arg
        // ...
    }
}';
         
        $this->assertFixResult($input, $expected);
    }

    public function testConstructorWithComplexTypesAndMessySpacing(): void
    {
        $input = '<?php
class Test {
    public function __construct(
        private FlatplanTypeRepository $flatplanTypeRepository,
    private ConcessionRepository $concessionRepository,
        private CracFilesystem $cracFilesystem,
    private SomeOtherService $someOtherService,
        private YetAnotherService $yetAnotherService) {
        parent::__construct();
    }
}';
        
        $expected = '<?php
class Test {
    public function __construct(
        private FlatplanTypeRepository $flatplanTypeRepository,
        private ConcessionRepository $concessionRepository,
        private CracFilesystem $cracFilesystem,
        private SomeOtherService $someOtherService,
        private YetAnotherService $yetAnotherService
    ) {
        parent::__construct();
    }
}';
         
        $this->assertFixResult($input, $expected);
    }

    public function testConstructorWithEverythingFuckedUp(): void
    {
        $input = '<?php
class Test {
    public function __construct($arg1,
     $arg2,             $arg3,
        $arg4,
            $arg5, $arg6,
                $arg7,	$arg8,	$arg9,
                    $arg10, $arg11, $arg12,
                        $arg13, $arg14, $arg15) {
        // ...
    }
}';
        
        $expected = '<?php
class Test {
    public function __construct(
        $arg1,
        $arg2,
        $arg3,
        $arg4,
        $arg5,
        $arg6,
        $arg7,
        $arg8,
        $arg9,
        $arg10,
        $arg11,
        $arg12,
        $arg13,
        $arg14,
        $arg15
    ) {
        // ...
    }
}';
         
        $this->assertFixResult($input, $expected);
    }

    private function assertFixResult(string $input, string $expected): void
    {
        $tokens = Tokens::fromCode($input);
        $this->fixer->fix(new \SplFileInfo('test.php'), $tokens);

        var_dump($input);
        var_dump($tokens->generateCode());
        
        $this->assertEquals($expected, $tokens->generateCode());
    }
}