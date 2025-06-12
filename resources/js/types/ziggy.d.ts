declare module 'ziggy-js' {
  export type RouteName = string;
  
  export interface Config {
    url: string;
    port: number | null;
    defaults: Record<string, any>;
    routes: Record<string, any>;
    location: string;
  }
  
  export type RouteParam = 
    | string
    | number
    | boolean
    | null
    | undefined;
    
  export type RouteParams<T = Record<string, any>> = 
    | T
    | Record<string, RouteParam>
    | RouteParam[]
    | undefined;
    
  export type ParameterValue = 
    | RouteParam
    | RouteParam[];
}

declare module '../../vendor/tightenco/ziggy' {
  import { RouteName, RouteParams, Config } from 'ziggy-js';
  
  export function route(
    name: undefined,
    params: undefined,
    absolute?: boolean,
    config?: Config
  ): any;
  
  export function route(
    name: any,
    params?: RouteParams<any>,
    absolute?: boolean,
    config?: Config
  ): string;
  
  export function route(
    name: any,
    params?: any,
    absolute?: boolean,
    config?: Config
  ): string;
}