# Ikarus SPS
Ikarus SPS is an attempt to run PHP on a device as independent SPS.

### Installation
This reporitory is a package that can be intalled using composer:
````bin
$ composer require ikarus/sps
````

#### How Ikarus SPS works

The Ikarus SPS engine distinguish between two kinds of run modes:
1.  __Cycle based engine__  
    This engine type defines a fixed frequency, for example 2Hz, which means, that the engine updates twice per second.
1.  __Event based engine__  
    The event based engine waits until an event gets triggered from one of its plugins.
    
So it depends of your requirements, which kind of SPS engine you prefer.

__General workflow__

1.  There is usually one instance of a SPS Engine running on a device.
1.  __Eventbased__: This instance needs two kinds of plugins: listeners and triggers.  
    __or cyclebased__: The instance needs only cycle plugins.
1.  The listener waits until something happens in the SPS to perform an action. (SPS to device)  
    Example: A timer event was triggered and the listener now instructs a motor to close the door.
1.  A trigger wait until something happens on the device and informs the SPS. (Device to SPS)  
    Example: An input sensor notifies that the door is closed or a timer triggers that time is up.
1.  Run the engine, now the plugins get active and the SPS runs.

#### Multiprocessing
PHP has an extension that allows forking a process.  
This feature is used to multitask the SPS Engine.  
All trigger plugins are dispatched to a separate process. Now they can wait for an event of the device, while the SPS Engine and its listeners continue working.

#### SPS Trigger Plugins
As described above, trigger plugins run in a separate process to avoid that the SPS Engine gets blocked because of a listener (Example: listening on a socket blocks the thread until something is available to read from the socket.)  

But this has one disadvantage:
- An SPS trigger does not share the same memory as the SPS Engine and its listeners.

This means:
- An SPS trigger gets a copy of the environment at engine start.  
    After that, everything it does (reading object properties, defining variables, etc) does not affect the SPS Engine anymore (and vice versa!)
    
To solve this problem, the Ikarus SPS package has dispatchable events that can be transmitted from and to SPS triggers.