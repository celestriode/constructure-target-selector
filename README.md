# Constructure: Target Selectors
This is an implementation of [Constructure](https://github.com/celestriode/constructure) for Minecraft's target selector format.

The target selector format in Minecraft is ill-defined and even dependent on the input itself. This makes it difficult to define a general structure as one-size-fits-all. Because of this, parsing an input and defining an expected structure are more involved than is desired.

## Getting started

Rather than starting with a constructure object, you will need to start with the target selector parser itself. The parser's options can be customized, and you'll find that changing the parser's options will be necessary for accurately parsing a Minecraft-specific input.

```php
$parser = new TargetSelectorParser();
```

## The parser

The general and default format for target selectors is:

```yaml
# @a[name1=value1,name2=!value2]

TARGETER:               @
TARGET:                 a
DELIMITER_OPEN:         [
parameter name (1):     name1
DESIGNATOR:             =
NEGATOR:                !
parameter value (1):    value1
SEPARATOR:              ,
parameter name (2):     name2
DESIGNATOR:             =
NEGATOR:                !
parameter value (2):    value2
DELIMITER_CLOSE:        ]
```

### Tokens

All tokens can be directly modified, except for the target. The following redundantly sets the tokens to their default values, but can be changed however necessary for this specific parser instance.

```php
$parser->targeter = '@';
$parser->delimiterOpen = '[';
$parser->delimiterClose = ']';
$parser->nestedDelimiterOpen = '{';
$parser->nestedDelimiterClose = '}';
$parser->designator = '=';
$parser->separator = ',';
$parser->negator = '!';
```

### Target types (p, e, a, r, s)

The target will instead make use of the values within the `SelectorTargets` dynamic registry, from the [Dynamic Minecraft Registries](https://github.com/celestriode/dynamic-minecraft-registries) repository. Note that all of these repositories are empty by default, to allow you to have specific values based on the version and edition of Minecraft.

If you have no need for dynamic population, you can simply add the standard target types to the registry directly. This must be done before validation. If you are not validating input through constructure, then this is not needed.

```php
SelectorTargets::get()->addValues('p', 'e', 'a', 'r', 's');
```

### Parameter overrides

The parser will attempt to automatically determine the type of value that the input is, based on utterly no context due to the ill-defined selector format. Expect, then, that it makes the wrong assumption.

To account for cases where the parser lacks, you can force a parameter of a specific name to adhere to a certain type. The `addOverride()` method takes in the name of the parameter (which accepts a dot-syntax to target nested parameters, like `root.child`), and a function to run that will return the value of the parameter.

For example, if `nbt={}` were in the input, the parser would default to using the "nested parameters" type instead of the "SNBT" type, which is incorrect. You can create an override for this case to always force it to be an SNBT value.

The following are overrides are useful for Java Edition 1.17.

```php
$parser->addOverride('nbt', function(TargetSelectorParser $parser, StringReader $reader, Parameter $parameter) {

    return $parser::parseValueAsSnbt($parser, $reader, $parameter);
});

$parser->addOverride('type', function(TargetSelectorParser $parser, StringReader $reader, Parameter $parameter) {

    return $parser::parseValueAsResourceLocation($parser, $reader, $parameter);
});
```

## Constructure

Now that the parser has been situated, you can create the constructure object.

```php
$constructure = new TargetSelectorConstructure($parser, new EventHandler());
```

### Global audits

By default, type matching is not performed. Using type matching as a global audit can be useful, but note that global audits apply to **all** constructure structures in the tree. To target **only** values for their types, the `StructureIsValue` audit can be used as a predicate for the `TypesMatch` audit.

Simultaneously, checking for the valid negation of values will not be performed by default, instead relying on the `Negation` audit. As with `TypesMatch`, you'll want to use `StructureIsValue` as a predicate to ensure only values can be targeted, and not other structures within the tree.

```php
$constructure->addGlobalAudit(TypesMatch::get()->addPredicate(StructureIsValue::get()));
$constructure->addGlobalAudit(Negatable::get()->addPredicate(StructureIsValue::get()));
```

## Parsing

Now you can parse the input to generate a traversable tree.

```php
$raw = "@e[tag=test]";

$input = $constructure->toStructure($raw);
```

`ConversionFailure` will be thrown if it fails to parse the input.

## Validating

If the input needs to be validated, then an expected structure is necessary. This must be started with a root `Selector` object. The root will house the variety of target selectors that are supported in whatever the context is for validation.

```php
$selector = Selector::root();
```

There are a variety of target selector types already provided with the library. They are:

- `PlayerSelector` (`Selector::name()`) - selects a target based on a player name.
- `UuidSelector` (`Selector::uuid()`) - selects a target based on a UUID. The format of the UUID is lenient (e.g., "1-2-3-4-5" is valid) as it is a Minecraft-specific construct.
- `DynamicSelector` (`Selector::dynamic()`) - the complex selector that starts with the TARGETER token (`@a[tag=test]`).

These can be added when necessary to the root. Nearly every bit of the structure can have an audit attached to it. With the following, the player name must be between 1 and 16 characters long.

```php
$selector->addAcceptedSelector(Selector::uuid());
$selector->addAcceptedSelector(
    Selector::name()->addAudit(new StringLength(new MinMaxBounds(1, 16)))
);
```

Dynamic selectors have numerous building options. Start by creating the builder. It takes in a registry for the acceptable target types, which you will have populated earlier.

```php
$dynamicSelector = Selector::dynamic(SelectorTargets::get());
```

Follow-up by adding a variety of acceptable parameters. Adding a value takes the name of the parameter, and the expected type of value. The value has a few options as well, which are validated independently of audits. Options include `negatable()` (e.g., `tag=!test`) and `supportsMultiple()` (e.g., `tag=a,tag=b`).

If a parameter name is `NULL`, then the input may have any name at all, as long as the value matches.

```php
$dynamicSelector->getParameters()->addValue('tag', Selector::string()->negatable()->supportMultiple());
$dynamicSelector->getParameters()->addValue('scores', Selector::nested()
    ->addValue(null, Selector::string()->addAudit(Numeric::get()))
);

$selector->addAcceptedSelector($dynamicSelector);
```

----

Finally, run the validator.

```php
$result = $constructure->validate($input, $selector);
```


## Putting it all together

```php
$raw = "@e[tag=test]";

// Prepare the parser.

$parser = new TargetSelectorParser();

$parser->addOverride('nbt', TargetSelectorParser::forceSnbt());
$parser->addOverride('type', TargetSelectorParser::forceValueUntil(
    $parser->separator,
    $parser->delimiterClose,
    $parser->nestedDelimiterClose
));

// Prepare the input.

$constructure = new TargetSelectorConstructure($parser, new EventHandler());
$constructure->addGlobalAudit(TypesMatch::get()->addPredicate(StructureIsValue::get()));
$constructure->addGlobalAudit(Negatable::get()->addPredicate(StructureIsValue::get()));

$input = $constructure->toStructure($raw);

// Prepare the validator.

SelectorTargets::get()->addValues('p', 'e', 'a', 'r', 's');

$expected = Selector::root(Selector::name());
$dynamicSelector = Selector::dynamic(SelectorTargets::get());

$dynamicSelector->getParameters()
    ->addValue('tag', Selector::string()->negatable())
    ->addValue('x', Selector::string())
    ->addValue('something', Selector::string()->addAudit(Numeric::get()));

$expected->addAcceptedSelector($dynamicSelector);

// Validate.

$result = $constructure->validate($input, $expected);

var_dump($result);
```

The following inputs would have the stated result:

```php
$a = 'Bob';                         // true
$b = '1-2-3-4-5';                   // false (UUID not allowed)
$c = '1g-2-3-4-5';                  // false (UUID not allowed)
$d = '@a';                          // true
$e = '@a[]';                        // true
$f = '@a[tag=test]';                // true
$g = '@a[tag=!test]';               // true 
$h = '@a[blargh=test]';             // false ("blargh" is not a valid key)
$i = '@a[x=!test]';                 // false ("x" is not negatable)
$j = '@a[tag=test,something=4]';    // true
$k = '@a[tag=test,something=test]'; // false ("something" must be numeric)
```