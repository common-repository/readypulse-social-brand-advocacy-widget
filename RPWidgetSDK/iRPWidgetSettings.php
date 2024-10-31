<?php

interface iRPWidgetSettings {

    /**
     * function getWidgetUrl
     * return string (the url of of widget)
     */
    function getWidgetUrl();

    /**
     * function useNativeLook
     * return boolean
     */
    function useNativeLook();

    /**
     * function getWidgetType
     * returns string ('feed', 'album', 'gallery')
     */
    function getWidgetType();

    /**
     * function getWidgetWidth
     * returns string (width of widget)
     */
    function getWidgetWidth();

     /**
     * function getWidgetHeight
     * returns string (height of widget)
     */
    function getWidgetHeight();

    /**
     * function getWidgetScope
     * returns string (scope of widget)
     */
    function getWidgetScope();

    /**
     * function showWidgetHeader
     * return boolean
     */
    function showWidgetHeader();

    /**
     * function showWidgetFooter
     * return boolean
     */
    function showWidgetFooter();
    
    /**
     * function getWidgetId
     * return string
     */
    function getWidgetId();
    
    /**
     * function getThemeId
     * return string
     */
    function getThemeId();
    
    /**
     * function getGetAgent
     * return string
     */
    function getAgent();
    
    /**
     * function getGetAgent
     * return string
     */
    function getRef();
}