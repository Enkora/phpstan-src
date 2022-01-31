<?php declare(strict_types = 1);

namespace PHPStan\Type\Php;

use DatePeriod;
use DateTime;
use DateTimeInterface;
use PhpParser\Node\Expr\StaticCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\DynamicStaticMethodReturnTypeExtension;
use PHPStan\Type\Generic\GenericObjectType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\NullType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;

class DatePeriodConstructorReturnTypeExtension implements DynamicStaticMethodReturnTypeExtension
{

	public function getClass(): string
	{
		return DatePeriod::class;
	}

	public function isStaticMethodSupported(MethodReflection $methodReflection): bool
	{
		return $methodReflection->getName() === '__construct';
	}

	public function getTypeFromStaticMethodCall(MethodReflection $methodReflection, StaticCall $methodCall, Scope $scope): Type
	{
		if (!isset($methodCall->getArgs()[0])) {
			return new ObjectType(DatePeriod::class);
		}

		$firstArgType = $scope->getType($methodCall->getArgs()[0]->value);
		if ((new StringType())->isSuperTypeOf($firstArgType)->yes()) {
			$firstArgType = new ObjectType(DateTime::class);
		}
		$thirdArgType = null;
		if (isset($methodCall->getArgs()[2])) {
			$thirdArgType = $scope->getType($methodCall->getArgs()[2]->value);
		}

		if (!$thirdArgType instanceof Type) {
			return new GenericObjectType(DatePeriod::class, [
				$firstArgType,
				new NullType(),
				new IntegerType(),
			]);
		}

		if ((new ObjectType(DateTimeInterface::class))->isSuperTypeOf($thirdArgType)->yes()) {
			return new GenericObjectType(DatePeriod::class, [
				$firstArgType,
				$thirdArgType,
				new NullType(),
			]);
		}

		if ((new IntegerType())->isSuperTypeOf($thirdArgType)->yes()) {
			return new GenericObjectType(DatePeriod::class, [
				$firstArgType,
				new NullType(),
				$thirdArgType,
			]);
		}

		return new ObjectType(DatePeriod::class);
	}

}