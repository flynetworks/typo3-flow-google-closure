##Google Closure Package for TYPO3 Flow

## Installation

I recommend to install this package using composer.<br />
Just require the <a href="https://packagist.org/packages/flynetworks/google-closure">flynetworks/google-closure</a>
package in your composer.json

```javascript
"require": {
    "flynetworks/google-closure": "2.*"
}
```


## Basic Configuration

After you have the package installed there is a new configuration file "GoogleClosure.yaml" available to you.<br />
This file has basically the following structure:

```
UniqueIdentifier:
  compiler:
    options:
```

### Example

```
MyUniqueKey:
  compiler:
    options:
      id: 'my-project-id'
      externs:
        - 'resource://My.Package/Public/JavaScripts/Externs/SomeExterns.js'
      paths:
        - 'resource://My.Package/Public/JavaScripts/'
      moduleOutputPath: 'resource://My.Package/Public/JavaScripts/%s.min.js'
      moduleProductionUri: 'resource://My.Package/Public/JavaScripts/%s.min.js'
      modules:
        'Application':
          deps: []
          inputs:
            - 'resource://My.Package/Public/JavaScripts/Application.js'
```

Don't use a dot "." for the UniqueIdentifier! I recommend to use only alphanumeric characters.<br />
Within the "options" property you can start to configure the compiler parameters.<br />

#### Available compiler options:
This package is using the <a href="http://plovr.com">plovr</a> build tool.
A documentation about<br />the options you can use is located here: http://plovr.com/options.html
<br />
In the list below all options are listed with their corresponding type.

<table width="100%">
	<tr>
    	<th>Option</th>
        <th>Type</th>
    </tr>
	<tr>
    	<td>id <small style="color: red;">required</small></td>
        <td>string</td>
    </tr>
	<tr>
    	<td>inputs</td>
        <td>array[string]</td>
    </tr>
	<tr>
    	<td>paths</td>
        <td>array[string]</td>
    </tr>
	<tr>
    	<td>externs</td>
        <td>array[string]</td>     
    </tr>
	<tr>
    	<td>customExternsOnly</td>
        <td>boolean</td>
    </tr> 
	<tr>
    	<td>closureLibrary</td>
        <td>string</td>
    </tr> 
	<tr>
    	<td>experimentalExcludeClosureLibrary</td>
        <td>boolean</td>  
    </tr>    
    <tr>
    	<td>mode</td>
        <td>RAW, WHITESPACE, SIMPLE, ADVANCED</td>
    </tr>
    <tr>
    	<td>level</td>
        <td>QUIET, DEFAULT, VERBOSE</td>
    </tr>
    <tr>
    	<td>inherits</td>
        <td>string</td>
    </tr>
    <tr>
    	<td>debug</td>
        <td>boolean</td>
    </tr>
    <tr>
    	<td>prettyPrint</td>
        <td>boolean</td>
    </tr>  
    <tr>
    	<td>printInputDelimiter</td>
        <td>boolean</td>
    </tr>  
    <tr>
    	<td>outputFile</td>
        <td>string</td>
    </tr>  
    <tr>
    	<td>outputWrapper</td>
        <td>array[string]</td>
    </tr>  
    <tr>
    	<td>outputCharset</td>
        <td>string</td>
    </tr>  
    <tr>
    	<td>fingerprint</td>
        <td>boolean</td>
    </tr>  
    <tr>
    	<td>modules</td>
        <td>object</td>
    </tr>  
    <tr>
    	<td>moduleOutputPath</td>
        <td>string</td>
    </tr>      
    <tr>
    	<td>moduleProductionUri</td>
        <td>string</td>
    </tr> 
    <tr>
    	<td>moduleInfoPath</td>
        <td>string</td>
    </tr> 
    <tr>
    	<td>globalScopeName</td>
        <td>string</td>
    </tr> 
    <tr>
    	<td>define</td>
        <td>object</td>
    </tr> 
    <tr>
    	<td>treatWarningsAsErrors</td>
        <td>boolean</td>
    </tr> 
    <tr>
    	<td>exportTestFunctions</td>
        <td>boolean</td>
    </tr> 
    <tr>
    	<td>nameSuffixesToStrip</td>
        <td>array[string]</td>
    </tr> 
    <tr>
    	<td>typePrefixesToStrip</td>
        <td>array[string]</td>
    </tr> 
    <tr>
    	<td>idGenerators</td>
        <td>array[string]</td>
    </tr>     
    <tr>
    	<td>ambiguateProperties</td>
    	<td>boolean</td>        
    </tr>
    <tr>
    	<td>disambiguateProperties</td>
    	<td>boolean</td>        
    </tr>
    <tr>
    	<td>experimentalCompilerOptions</td>
    	<td>object</td>        
    </tr>
    <tr>
    	<td>customPasses</td>
    	<td>object</td>        
    </tr>
    <tr>
    	<td>soyFunctionPlugins</td>
    	<td>array[string]</td>        
    </tr>
    <tr>
    	<td>jsdocHtmlOutputPath</td>
    	<td>string</td>        
    </tr>
    <tr>
    	<td>variableMapInputFile</td>
    	<td>string</td>        
    </tr>
    <tr>
    	<td>variableMapOutputFile</td>
    	<td>string</td>        
    </tr>
    <tr>
    	<td>propertyMapInputFile</td>
    	<td>string</td>        
    </tr>
    <tr>
    	<td>propertyMapOutputFile</td>
    	<td>string</td>        
    </tr> 
    <tr>
    	<td>testTemplate</td>
        <td>string</td>
    </tr>
    <tr>
    	<td>testExcludes</td>
        <td>array[string]</td>
    </tr>
</table>

## Advanced Configuration

There are some more configuration properties available to you.
Just see the <b>example</b> below.<br />

```
MyFancyConfiguration:
  embedMode: 'dependency' #Viewhelper renders the dependency version
  dependency:
    command: 'python %FLOW_PATH_PACKAGES%Application/My.Cool.Package/Resources/Public/closure/bin/build/depswriter.py'
    outputFileName: 'MyCustomDepsFileName.js'
  compiler:
    command: 'java -jar %FLOW_PATH_PACKAGES%Application/My.Cool.Package/Resources/Private/Bin/Plovr.jar build'
    
ChildConfiguration:
  embedMode: 'compiled' #Viewhelper renders the compiled version
  extends: 'MyFancyConfiguration'
  compiler:
   command: 'java -jar %FLOW_PATH_PACKAGES%Application/Super.Child.Package/Resources/Private/Bin/Plovr.jar build'
```

## ViewHelper

Also a ViewHelper "EmbedScriptViewHelper" is available through this package :)

```
{namespace gc=FlyNetworks\Google\Closure\ViewHelpers}
<!DOCTYPE html>
<html>
	<head>
        <gc:embedScript configurationKey="MyFancyConfiguration" />
        <gc:embedScript configurationKey="ChildConfiguration" />        
	</head>
</html>
```

## Commands

```
./flow closure:compile
./flow closure:dependency
```