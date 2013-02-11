<?php
/*
File Name: getweather.php
Author: Gary White
Original version: May 12, 2005
Last modified: August 7, 2008

Copyright (C) 2004-2008 Gary White

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License in the included gpl.txt file for
details.

See http://apptools.com/phptools/xml/weather/ for usage information

See http://weather.gov/data/current_obs/
Also see 
	http://weather.gov/alerts/
	http://weather.gov/forecasts/xml/

Complete list of Weather stations available at 
	http://weather.gov/data/current_obs/index.xml

*/
class GetWeather {

    // Initialize some variables
    static $itemdata;
    static $itemname;
    static $wxdata;


    function get($rpt) {
        
        // URL for the XML file
        $xmlurl="http://www.weather.gov/data/current_obs/$rpt.xml";

        // Base url for the icons
        $imgpath="http://weather.gov/weather/images/fcicons";

        
        self::$itemdata="";
        self::$itemname="";
        self::$wxdata=array();
  
        $icons=self::defineIcons();
        $icon="";
        $data="";
        $report="";
        
        // create a new CURL resource
        if($ch = curl_init()) {

            // set URL and other appropriate options
            curl_setopt($ch, CURLOPT_URL, $xmlurl);
            curl_setopt($ch, CURLOPT_HEADER, trus);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            
            // grab URL and pass it to the browser
            $data=curl_exec($ch);

            $r=curl_getinfo($ch); //,CURLINFO_HTTP_CODE);

            // close CURL resource, and free up system resources
            curl_close($ch);

            // Create an XML parser
            $xml_parser = xml_parser_create();
            
            // Use case-folding so we are sure to find the tag in $map_array
            // This will force all tags to upper case so we don't have to worry
            // about matching the case of the original in our tests.
            xml_parser_set_option($xml_parser, XML_OPTION_CASE_FOLDING, true);
            
            // Assign the element starting and ending event handlers
            xml_set_element_handler($xml_parser, array(self,"startElement"), array(self,"endElement"));
            
            // Assign a function to handle character data
            xml_set_character_data_handler($xml_parser, array(self,"characterData"));
            
            // Parse the file. This will place the data into an associative
            // array assigned to the self::$wxdata variable
            xml_parse($xml_parser,$data,true);
            
            // Free the parser object
            xml_parser_free($xml_parser);
            
            // The OBSERVATION_TIME field of the returned XML will be in the
            // format "Last Updated on May 18, 8:53 am CDT"
            // We're going to change the format a bit.

            // Strip out the "Last Updated on " portion of the date/time
            // so we can display that separately in our tabular output
            $datetime=str_replace("Last Updated on ","",self::$wxdata['OBSERVATION_TIME']);
            
            // We now have the format as "May 18, 8:53 am CDT"
            // Now, get the time zone. It will be everything from
            // the last space character to the end of the string.
            $z=strrchr($datetime," ");

            // Get the current year
            $year=date("Y");

            // Now, we stuff the year into the string, following the comma.
            $datetime=str_replace(",",", $year",$datetime);

            // This does leave a small potential issue where, if you get a 
            // report between midnight and 1 a.m. on January 1, or the server
            // is in a significantly different time zone than the report it 
            // could be as late as 4 a.m. the year will be wrong because the 
            // report will be from the previous year. I suppose it would be 
            // possible to correct for that, but for that little bit, I'm 
            // not going to worry about it.

            // Now, strip out the time zone
            $datetime=str_replace($z,"",$datetime);

            // Format the date and time the way we want it and add
            // back the time zone
            $datetime=date("l F j, Y g:i A",strtotime($datetime)).$z;
            self::$wxdata['OBSERVATION_TIME']=$datetime;
            
            // Get the WEATHER element
            $wx=trim(self::$wxdata['WEATHER']);

            // Now, get the icon to match the weather
            foreach($icons as $k=>$i){
                $a=explode(" | ",$i);
                if(is_numeric(array_search($wx,$a))){
                    self::$wxdata['ICON']="$imgpath/$k.jpg";
                    break;
                }
            }

            // Replace any null elements with "Not available"
            foreach(array_keys(self::$wxdata) as $key){
                self::$wxdata[$key]=self::$wxdata[$key]=="NULL"?"Not available":self::$wxdata[$key];
            }

            // If we got humidity
            if(is_numeric(self::$wxdata['RELATIVE_HUMIDITY']))
                // Append a percent sign
                self::$wxdata['RELATIVE_HUMIDITY'].="%";

            // Do some formatting to make the output a little friendlier
            if(self::$wxdata['VISIBILITY_MI']=="NA")
                self::$wxdata['VISIBILITY']="Not available";
            if(self::$wxdata['VISIBILITY']!="Not available")
                self::$wxdata['VISIBILITY']=(1*self::$wxdata['VISIBILITY_MI'])." miles";

            // If we got wind data
            if(is_numeric(self::$wxdata['WIND_MPH'])){
                // We're going to output wind data as both MPH from a cardinal direction
                // and as Knots from a direction in degrees

                // Calculate the value for Knots
                self::$wxdata['WIND_KNOTS']=self::$wxdata['WIND_MPH']/1.15;

                // Format the output
                $wind=sprintf("From the %s at %d mph (%03.0f&deg; at %d knots)",self::$wxdata['WIND_DIR'],self::$wxdata['WIND_MPH'],self::$wxdata['WIND_DEGREES'],self::$wxdata['WIND_KNOTS']);

                // If we got a value for wind gusts
                if(is_numeric(self::$wxdata['WIND_GUST_MPH']) && self::$wxdata['WIND_GUST_MPH']>0){
                    // add it into the wind string
                    $wind=str_replace("mph","gusting to ".self::$wxdata['WIND_GUST_MPH']." mph<br>", $wind);
                    $knots=sprintf("%d",self::$wxdata['WIND_GUST_MPH']/1.15);
                    $wind=str_replace("knots","gusting to $knots knots",$wind);
                }
            } else {
                // Otherwise, if wind is zero, we'll show "Calm"
                $wind=self::$wxdata['WIND_MPH']=="Not available"?"Not available":"Calm";
            } // Done with wind
            self::$wxdata['WIND_STRING']=$wind;

        } // Done getting and formatting the data
        return self::$wxdata;
    }
    
    function startElement($parser, $name, $attrs) {
        self::$itemname=$name;
        self::$itemdata="";
    }

    function endElement($parser, $name) {
        self::$wxdata[self::$itemname]=self::$itemdata;
        self::$itemdata="";
    }

    function characterData($parser, $data) {
        self::$itemdata.=$data;
    }

    function defineIcons(){
        // See http://weather.gov/data/current_obs/weather.php for source data for this function
        $retVal['bkn']="Mostly Cloudy | Mostly Cloudy with Haze | Mostly Cloudy and Breezy";
        $retVal['skc']="Fair | Clear | Fair with Haze | Clear with Haze | Fair and Breezy | Clear and Breezy";
        $retVal['few']="A Few Clouds | A Few Clouds with Haze | A Few Clouds and Breezy";
        $retVal['sct']="Partly Cloudy | Party Cloudy with Haze | Partly Cloudy and Breezy";
        $retVal['ovc']="Overcast | Overcast with Haze | Overcast and Breezy";
        $retVal['nfg']="Fog/Mist | Fog | Freezing Fog | Shallow Fog | Partial Fog | Patches of Fog | Fog in Vicinity | Freezing Fog in Vicinity | Shallow Fog in Vicinity | Partial Fog in Vicinity | Patches of Fog in Vicinity | Showers in Vicinity Fog | Light Freezing Fog | Heavy Freezing Fog";
        $retVal['smoke']="Smoke";
        $retVal['fzra']="Freezing Rain | Freezing Drizzle | Light Freezing Rain | Light Freezing Drizzle | Heavy Freezing Rain | Heavy Freezing Drizzle | Freezing Rain in Vicinity | Freezing Drizzle in Vicinity";
        $retVal['ip']="Ice Pellets | Light Ice Pellets | Heavy Ice Pellets | Ice Pellets in Vicinity | Showers Ice Pellets | Thunderstorm Ice Pellets | Ice Crystals | Hail | Small Hail/Snow Pellets | Light Small Hail/Snow Pellets | Heavy Small Hail/Snow Pellets | Showers Hail | Hail Showers";
        $retVal['mix']="Freezing Rain Snow | Light Freezing Rain Snow | Heavy Freezing Rain Snow | Freezing Drizzle Snow | Light Freezing Drizzle Snow | Heavy Freezing Drizzle Snow | Snow Freezing Rain| Light Snow Freezing Rain | Heavy Snow Freezing Rain | Snow Freezing Drizzle | Light Snow Freezing Drizzle | Heavy Snow Freezing Drizzle";
        $retVal['raip']="Rain Ice Pellets | Light Rain Ice Pellets | Heavy Rain Ice Pellets | Drizzle Ice Pellets | Light Drizzle Ice Pellets | Heavy Drizzle Ice Pellets | Ice Pellets Rain | Light Ice Pellets Rain | Heavy Ice Pellets Rain | Ice Pellets Drizzle | Light Ice Pellets Drizzle | Heavy Ice Pellets Drizzle";
        $retVal['rasn']="Rain Snow | Light Rain Snow | Heavy Rain Snow | Snow Rain | Light Snow Rain | Heavy Snow Rain | Drizzle Snow | Light Drizzle Snow | Heavy Drizzle Snow | Snow Drizzle | Light Snow Drizzle | Heavy Snow Drizzle";
        $retVal['shra']="Rain Showers | Light Rain Showers | Heavy Rain Showers | Rain Showers in Vicinity | Light Showers Rain | Heavy Showers Rain | Showers Rain | Showers Rain in Vicinity | Rain Showers Fog/Mist | Light Rain Showers Fog/Mist | Heavy Rain Showers Fog/Mist | Rain Showers in Vicinity Fog/Mist | Light Showers Rain Fog/Mist | Heavy Showers Rain Fog/Mist | Showers Rain Fog/Mist | Showers Rain in Vicinity Fog/Mist";
        $retVal['tsra']="Thunderstorm | Light Thunderstorm Rain | Heavy Thunderstorm Rain | Thunderstorm Rain Fog/Mist | Light Thunderstorm Rain Fog/Mist | Heavy Thunderstorm Rain Fog/Mist | Thunderstorm Showers in Vicinity | | Light Thunderstorm Rain Haze | Heavy Thunderstorm Rain Haze | Thunderstorm Fog | Light Thunderstorm Rain Fog | Heavy Thunderstorm Rain Fog | Thunderstorm Light Rain | Thunderstorm Heavy Rain | Thunderstorm Rain Fog/Mist | Thunderstorm Light Rain Fog/Mist | Thunderstorm Heavy Rain Fog/Mist | Thunderstorm in Vicinity Fog/Mist | Thunderstorm Showers in Vicinity | Thunderstorm in Vicinity | Thunderstorm in Vicinity Haze | Thunderstorm Haze in Vicinity | Thunderstorm Light Rain Haze | Thunderstorm Heavy Rain Haze | Thunderstorm Fog | Thunderstorm Light Rain Fog | Thunderstorm Heavy Rain Fog | Thunderstorm Hail | Light Thunderstorm Rain Hail | Heavy Thunderstorm Rain Hail | Thunderstorm Rain Hail Fog/Mist | Light Thunderstorm Rain Hail Fog/Mist | Heavy Thunderstorm Rain Hail Fog/Mist | Thunderstorm Showers in Vicinity Hail | | Light Thunderstorm Rain Hail Haze | Heavy Thunderstorm Rain Hail Haze | Thunderstorm Hail Fog | Light Thunderstorm Rain Hail Fog | Heavy Thunderstorm Rain Hail Fog | Thunderstorm Light Rain Hail | Thunderstorm Heavy Rain Hail | Thunderstorm Rain Hail Fog/Mist | Thunderstorm Light Rain Hail Fog/Mist | Thunderstorm Heavy Rain Hail Fog/Mist | Thunderstorm in Vicinity Hail Fog/Mist | Thunderstorm Showers in Vicinity Hail | Thunderstorm in Vicinity Hail | Thunderstorm in Vicinity Hail Haze | Thunderstorm Haze in Vicinity Hail | Thunderstorm Light Rain Hail Haze | Thunderstorm Heavy Rain Hail Haze | Thunderstorm Hail Fog | Thunderstorm Light Rain Hail Fog | Thunderstorm Heavy Rain Hail Fog | Thunderstorm Small Hail/Snow Pellets | Thunderstorm Rain Small Hail/Snow Pellets | Light Thunderstorm Rain Small Hail/Snow Pellets | Heavy Thunderstorm Rain Small Hail/Snow Pellets";
        $retVal['sn']="Snow | Light Snow | Heavy Snow | Snow Showers | Light Snow Showers | Heavy Snow Showers | Showers Snow | Light Showers Snow | Heavy Showers Snow | Snow Fog/Mist | Light Snow Fog/Mist | Heavy Snow Fog/Mist | Snow Showers Fog/Mist | Light Snow Showers Fog/Mist | Heavy Snow Showers Fog/Mist | Showers Snow Fog/Mist | Light Showers Snow Fog/Mist | Heavy Showers Snow Fog/Mist | Snow Fog | Light Snow Fog | Heavy Snow Fog | Snow Showers Fog | Light Snow Showers Fog | Heavy Snow Showers Fog | Showers Snow Fog | Light Showers Snow Fog | Heavy Showers Snow Fog | Showers in Vicinity Snow | Snow Showers in Vicinity | Snow Showers in Vicinity Fog/Mist | Snow Showers in Vicinity Fog | Low Drifting Snow | Blowing Snow | Snow Low Drifting Snow | Snow Blowing Snow | Light Snow Low Drifting Snow | Light Snow Blowing Snow | Heavy Snow Low Drifting Snow | Heavy Snow Blowing Snow | Thunderstorm Snow | Light Thunderstorm Snow | Heavy Thunderstorm Snow | Snow Grains | Light Snow Grains | Heavy Snow Grains | Heavy Blowing Snow | Blowing Snow in Vicinity";
        $retVal['wind']="Windy | Fair and Windy | A Few Clouds and Windy | Partly Cloudy and Windy | Mostly Cloudy and Windy | Overcast and Windy";
        $retVal['hi_shwrs']="Showers in Vicinity | Showers in Vicinity Fog/Mist | Showers in Vicinity Fog | Showers in Vicinity Haze";
        $retVal['fzrara']="Freezing Rain Rain | Light Freezing Rain Rain | Heavy Freezing Rain Rain | Rain Freezing Rain | Light Rain Freezing Rain | Heavy Rain Freezing Rain | Freezing Drizzle Rain | Light Freezing Drizzle Rain | Heavy Freezing Drizzle Rain | Rain Freezing Drizzle | Light Rain Freezing Drizzle | Heavy Rain Freezing Drizzle";
        $retVal['hi_tsra']="Thunderstorm in Vicinity | Thunderstorm in Vicinity Fog/Mist | Thunderstorm in Vicinity Fog | Thunderstorm Haze in Vicinity | Thunderstorm in Vicinity Haze";
        $retVal['ra1']="Light Rain | Drizzle | Light Drizzle | Heavy Drizzle | Light Rain Fog/Mist | Drizzle Fog/Mist | Light Drizzle Fog/Mist | Heavy Drizzle Fog/Mist | Light Rain Fog | Drizzle Fog | Light Drizzle Fog | Heavy Drizzle Fog";
        $retVal['ra']="Rain | Heavy Rain | Rain Fog/Mist | Heavy Rain Fog/Mist | Rain Fog | Heavy Rain Fog";
        $retVal['nsvrtsra']="Funnel Cloud | Funnel Cloud in Vicinity | Tornado/Water Spout";
        $retVal['dust']="Dust | Low Drifting Dust | Blowing Dust | Sand | Blowing Sand | Low Drifting Sand | Dust/Sand Whirls | Dust/Sand Whirls in Vicinity | Dust Storm | Heavy Dust Storm | Dust Storm in Vicinity | Sand Storm | Heavy Sand Storm | Sand Storm in Vicinity";
        $retVal['mist']="Haze";
        return $retVal;
    }
// end CLASS
}
?>
