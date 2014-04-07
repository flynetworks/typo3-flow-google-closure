#Lightweight Google Closure Package for TYPO3 Flow

## This package is also available as composer package:
https://packagist.org/packages/flynetworks/google-closure

##### Example Configuration (Settings.yaml)

```lang
FlyNetworks:
  Google:
    Closure:
      DependencyBuilder:
        Your.Unique.Key.Or.Package.Key:
          OutputFileName: 'MyDepsFile.js'
      Compiler:
        Your.Unique.Key.Or.Package.Key:
          Id: 'your-unique-key-or-package-key'
          ModuleOutputPath: 'resource://Your.Package/Public/JavaScripts/%s.min.js'
          Paths:
            - 'resource://Your.Package/Public/JavaScripts/'
          Modules:
            Application:
              Inputs: 'resource://Your.Package/Public/JavaScripts/Application.js'
              Deps: []
```

The compiler is using the http://plovr.com build tool.
For a full list of configuration options and additional support please see http://plovr.com/docs.html.

**Notes:**
> The configuration options in the "Settings.yaml" are automatically converted from 
> the CamelCase notation to the documented dash ("-") notation.
> The compiler has the following default setup:
https://github.com/flynetworks/typo3-flow-google-closure/blob/master/Configuration/Settings.yaml


##### Available CommandLine Commands
```lang
./flow closure:compile
./flow closure:deps
```
