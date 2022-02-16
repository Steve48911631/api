# CakePHP Application API REST

## INSTALLATION
```sh
1. install GIT on your machine
2. create the directory where you want to install the program
3. open your cmd terminal and write the following commands:
	cd your_folder
	git clone https://github.com/Steve48911631/api.git
4. project is downloaded and installed
```
### Alternatively
```sh
1. unzip the project inside your project folder
```

## PUT YOUR DATA
```sh
DB data are stored in a CSV file.
You can replace with your own file in the following folder: /resources/

File MUST be named as "data.csv", in this way data will be loaded automatically.
```
### Alternatively
```sh
you can change the name of the csv file following the CONFIGURATION section
```
## CONFIGURATION

Some default configurations can be applied here:

### Path
```sh
	src/Controller/FlyersController.php 
	initialize() method
```
1. file: can be changed the name of the file or its destination.	

2. allowed_filter: Array where can be specified which columns are accepted in the GET param "filter[]"

3. Initial_filters: Array composed as follow:

		[

			'column_name'=>[

				'operand'=>'<=', //can be ==,!=,>=,<=,>,<

                    		'value'=>'value to compare',

			]

		]
	
this setting apply the default filters to data in the flyers.json endpoint 	

as default is applied a filter with "start_date <= CURRENT_DATE <= end_date"		
	

## USAGE 

### Endpoints

1. GET request at: <a href="">/flyers.json</a>
```sh
Params:
	- page=1 		(default)
	- limit=100 		(default)
	- fields=id,category 	(if omitted shows all fields)
	- filter[field]=value	(if omitted no filter applied)
	
```

2. GET request at: <a href="">/flyers/{{id}}.json</a>
```sh
Params:
	- fields=id,category 	(if omitted shows all fields)
	
```

