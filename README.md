# PHP-File Class

[TOC]



PHP-File class was developed to simplify the handling of files.
With PHP-File class you can read, decrypt, encrypt, move, write, copy and delete all kinds of files. Additionally, new files can be created that are temporarily accessible and can then be saved under a specific directory.


## Install

The install is simple.

Requirement is at least PHP 5.3.3, openssl with the sodium package.

### By Hand

Move the files to the appropriate directory

```php
require_once('vendor/autoload.php');

# namespace
use JacksonJeans;

# open file
$File = new JacksonJeans\File;
$File->setFile('test/Test.txt');

# create file
$File = new JacksonJeans\File('mynewfile','txt',$binary = false);
$File->write("Test\nJacksonJeans");

# save with move, because the file is in tempDir
$File->move('test/');
```

### Composer

Or with Composer

```bash
$ composer install
```

## Usage

The use is simple

### Open

Set using path.

```php
# namespace
use JacksonJeans;

# open file
$File = new JacksonJeans\File;
$File->setFile('test/Test.txt');
```

### Read

Read entire file.

```php
$str = $File->read();
```

### ReadByLine

Read line by line.

```php
# line by line
$str = '';
while(($line = $File->readLine())!== false){
    $str .= $line;
}

# a specific line 
$str = $File->readLine($int = 1);
```

### Write

Write an new line.

```php
$File->write("Test\n");
```

### Overwrite

Overwrite your file.

```php
$File->write("Test\n", $overwrite = true);
```
### Create new file

Create a new file.

```php
$File = new JacksonJeans\File('mynewfile','txt',$binary = false);
```
###  Move

Move the file to an other destination.

```php
$File->move('JacksonJeans/');
```

###  Copy

Copy the file to an other destination.

```php
$File->copy('JacksonJeans/');
```

###  Delete

Delete the file.

```php
$File->delete();
```

###  Store

Store your file.

```php
# Close file if you created it in the specified directory.
$File->close();

# Recommendation: If you have created a new file, move it using the constructor.
$File->move('JacksonJeans/');
```

###  Decompress zipFile

Decompress file if the file is a zipArchiv.

```php
# Returns a JacksonJeans\FileList and saves the file to the specified destination.
$results = $File->decompress('JacksonJeans/');

# Returns an array with JacksonJeans\File elements and saves the file to the specified destination with new name.
$results = $File->decompress('JacksonJeans/archiv.zip');
```

###  Decrypt

Decrypt file content.

```php
# get decrypted string
$str = $File->decrypt($key = '', $iv = '', $overwrite = false );

# overwrite File with decrypted content
$File->decrypt($key = '', $iv = '', $overwrite = true);
```
###  Encrypt

Encrypt file content.

```php
# get encrypted string
$str = $File->encrypt($key = '', $iv = '', $overwrite = false );

# overwrite File with encrypted content
$File->encrypt($key = '', $iv = '', $overwrite = true);
```

### search

Search the entire file

```php
$searchStr = "Test\n";
$search = $File->search($searchStr);

# If false, then no result
if($search){
    foreach ($search as $offset) {
        $offset = (int)$offset;
        echo "\nThe searchstring: {$searchStr} was found at position of the '$offset'.nd byte.\n";
    }
}
```

###  setPointer

Set file pointer.

```php
# set pointer
# return false if error
$File->setPointer($int = 0);
```

###  getPointer

Get file pointer.

```php
# get pointer
$int = $File->getPointer();
```

###  resetPointer

Reset file pointer.

```php
# reset pointer
# return false if error
$File->resetPointer();
```

## Encryption & Decryption

The encryption and decryption is done by the [AESCryptoStreamFactory](src/AESCryptoStreamFactory.php) class. This implements Sodium and OpenSSL as well as the PKCS_5 padding. PKCS_5 padding is the same as PKCS_7 padding except that the block size is not variable and is set to [BLOCK_SIZE::int] 8 by a constant in the [AESCryptoStreamFactory](src/AESCryptoStreamFactory.php) class. 

The vector length [IV_LENGTH::int] is set to 16 characters.

## Decompression & Compression

Currently, only the .zip format is implemented using \ZipArchive. The .rar, .gzip and .tar implementation is still to come.

Compressing and decompressing can be done using the JacksonJeans\FileList class. JacksonJeans\File can decompress only if a .zip - archive was passed.

## FileList

JacksonJeans\FileList represents archives.
Using FileList, archives can be created by the files that are added to it or by specifying a path. FileList can compress whole directories including subdirectories.

### Create an archive

```php
# new archiv
$FileList = new JacksonJeans\FileList;

$FileList->setName('exampleArchiv');

# must be of type JacksonJeans\File class
# e.g $results from 

foreach($results as $File){
	$FileList->append($File);
}

$FileList->compress('test/archiv/');

# overwrite if exist
$FileList->compress('test/archiv/', $overwrite = true);
```

### Decompress an archive

```php
# set archiv
$FileList = new JacksonJeans\FileList;
$FileList->setArchive('test/archive/exampleArchiv.zip');

# Returns an array with JacksonJeans\File elements and saves the file to the specified destination with new name.
$results = $FileList->decompress('test/archive/exampleArchiv');
```

### listArchiveFiles

```php
# set archiv
$FileList = new JacksonJeans\FileList;
$FileList->setArchive('test/archive/exampleArchiv.zip');

# Returns an array with strings containing the file path.
$array = $FileList->listArchiveFiles();
```

### Create an archive from directory

```php
$FileList = new JacksonJeans\FileList;
$FileList->setName('allFiles');

# Returns $FileList if no error occurs, FALSE if error occurs.
$result = $FileList->setArchiveFromDir($sourcePath = 'test/',$destination = 'test/archive/');

```

