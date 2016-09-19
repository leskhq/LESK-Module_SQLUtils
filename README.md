# SQLUtils module

To deploy simply clone the repository into the ```Modules``` directory from the base or root [L51ESK](https://github.com/sroutier/laravel-5.1-enterprise-starter-kit) install, as shown below:
```
$ cd <MySuperProjectBasedOnL51ESK>
$ git clone https://github.com/sroutier/L51ESK-Module_SQLUtils app/Modules/SQLUtils
```

Then make sure to optimize the master module definition, from the base directory, with:
```
$ ./artisan module:optimize
```

# Dependencies
None. 

# Prerequisites
* A driver or library must be installed and configured on the system to allow communications with the SQL server of 
choice. In the case where FreeTDS is selected, a sample configuration file is provided in the ```misc/``` directory
to set the protocol version to ```7.3```.

# Installing and activating
Once a new module is detected by the framework, a site administrator can go to the "Modules administration" page 
and first initialize the module, then enable it for all authorized users to have access.
  
  
