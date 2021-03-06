<?php
declare(strict_types = 1);
/**
 * /tests/Integration/Rest/Traits/Actions/Anon/ActionTest.php
 *
 * @author  TLe, Tarmo Leppänen <tarmo.leppanen@protacon.com>
 */
namespace App\Tests\Integration\Rest\Traits\Actions\Anon;

use App\Utils\Tests\PhpUnitUtil;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ActionTest
 *
 * @package App\Tests\Integration\Rest\Traits\Actions\Anon
 * @author  TLe, Tarmo Leppänen <tarmo.leppanen@protacon.com>
 */
class ActionTest extends KernelTestCase
{
    /**
     * @dataProvider dataProviderTestThatTraitCallsExpectedMethod
     *
     * @param string $className
     * @param string $method
     * @param string $traitMethod
     * @param array  $parameters
     */
    public function testThatTraitCallsExpectedMethod(
        string $className,
        string $method,
        string $traitMethod,
        array $parameters
    ): void {
        $stub = $this->getMockForTrait(
            $className,
            [],
            '',
            true,
            true,
            true,
            [$traitMethod]
        );

        $stub
            ->expects(static::once())
            ->method($traitMethod)
            ->with(...$parameters);

        $result = \call_user_func_array([$stub, $method], $parameters);

        static::assertInstanceOf(Response::class, $result);
    }

    /**
     * @return array
     */
    public function dataProviderTestThatTraitCallsExpectedMethod(): array
    {
        self::bootKernel();

        $folder = static::$kernel->getRootDir() . '/Rest/Traits/Actions/Anon/';
        $pattern = '/^.+\.php$/i';

        $namespace = '\\App\\Rest\\Traits\\Actions\\Anon\\';

        $iterator = function (string $filename) use ($folder, $namespace): array {
            $base = \str_replace([$folder, \DIRECTORY_SEPARATOR, '.php'], ['', '\\', ''], $filename);
            $class = $namespace . $base;

            $parameters = [
                $request = $this->createMock(Request::class),
            ];

            switch ($base) {
                case 'CreateAction':
                    $parameters[] = $this->createMock(FormFactoryInterface::class);
                    break;
                case 'PatchAction':
                case 'UpdateAction':
                    $parameters[] = $this->createMock(FormFactoryInterface::class);
                    $parameters[] = Uuid::uuid4()->toString();
                    break;
                case 'DeleteAction':
                case 'FindOneAction':
                    $parameters[] = Uuid::uuid4()->toString();
                    break;
            }

            return [
                $class,
                \lcfirst($base),
                \str_replace('Action', 'Method', \lcfirst($base)),
                $parameters,
            ];
        };

        return \array_map($iterator, PhpUnitUtil::recursiveFileSearch($folder, $pattern));
    }
}
