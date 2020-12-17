import React, {Component} from 'react';

export default class JsonDisplay extends Component {
    
    render() {
        console.log(this.props.displayObject);
        return <div className="json-display">
            {
                Array.isArray(this.props.displayObject) 
                    ? this.displayArray(this.props.displayObject) 
                    : this.displayObject(this.props.displayObject)
            }
        </div>;
    }

    displayObject(displayObject, level = 0) {
        return (
            <div className={"json-object"}>
                <span className="curly-brace">{"{"}</span>
                    {this.displayIterableWithKeys(
                        displayObject, 
                        Object.keys(displayObject), 
                        level
                    )}
                <span className="curly-brace">{"}"}</span>
            </div>
        );
    }

    displayArray(displayObject, level = 0) {
        return (
            <div className={"json-array"}>
                <span className="bracket">{"["}</span>
                    {this.displayIterableWithoutKeys(
                        displayObject, 
                        [...displayObject.keys()], 
                        level
                    )}
                <span className="bracket">{"]"}</span>
            </div>
        );
    }

    displayNumber(value) {
        return (
            <span className="json-value json-number">{value}</span>
        );
    }

    displayBoolean(value) {
        return (
            <span className="json-value json-boolean">{value}</span>
        );
    }

    displayFunction(value) {
        return (
            <span className="json-value json-function"><pre>{value.toString()}</pre></span>
        );
    }

    displayString(value) {
        return (
            <span className="json-value json-string">"{value.trim()}"</span>
        );
    }

    displayUndefined() {
        return (
            <span className="json-value json-undefined">undefined</span>
        );
    }

    displayDefault(value) {
        return (
            <span className="json-value json-default">{value}</span>
        );
    }

    displayIterableWithKeys(displayObject, objectKeys, level) {
        return objectKeys.map((key) => {
            return (
                <div key={'row' + key + level} className={"json-row json-level-" + level}>
                    <span className="json-key">{key}:</span>
                    {this.displayValue(displayObject, key, level)}
                </div>
            );
        });
    }

    displayIterableWithoutKeys(displayObject, objectKeys, level) {
        return objectKeys.map((key) => {
            return (
                <div className={"json-row json-level-" + level}>
                    {this.displayValue(displayObject, key, level)}
                </div>
            );
        });
    }

    displayValue(displayObject, key, level) {
        switch (typeof (displayObject[key])) {
            case 'object':
                if (Array.isArray(displayObject[key])) {
                    return this.displayArray(displayObject[key], level + 1);
                }
                return this.displayObject(displayObject[key], level + 1);
            case 'function':
                return this.displayFunction(displayObject[key]);
            case 'string':
                return this.displayString(displayObject[key]);
            case 'boolean':
                return this.displayBoolean(displayObject[key]);
            case 'number':
                return this.displayNumber(displayObject[key]);
            case 'undefined':
                return this.displayUndefined();
            default:
                return this.displayDefault(displayObject[key]);
        }
    }
}
