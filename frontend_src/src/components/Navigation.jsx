import React, {Component} from 'react';
import NavLink from './NavLink.jsx';

export default class Navigation extends Component {
    constructor (props) {
        super(props);
        this.state = {
            tabs: {
                listRequests: {
                    name: 'listRequests', 
                    text: 'List requests', 
                    id: 'nav-list-requests-button', 
                    selectedClass: 'active'
                },
                listExpectations: {
                    name: 'listExpectations', 
                    text: 'List expectations', 
                    id: 'nav-list-expectations-button', 
                    selectedClass: ''
                },
                addExpectation: {
                    name: 'addExpectation', 
                    text: 'Add expectation', 
                    id: 'nav-add-expectation-button', 
                    selectedClass: ''
                },
            }
        };
    }

    render() {
        console.log(this.state);
        return (
            <nav className="navbar navbar-inverse navbar-fixed-top">
                <div className="container">
                    <div className="navbar-header">
                        <button type="button" className="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
                            <span className="sr-only">Toggle navigation</span>
                            <span className="icon-bar"></span>
                            <span className="icon-bar"></span>
                            <span className="icon-bar"></span>
                        </button>
                        <a className="navbar-brand" href="#">Phiremock</a>
                    </div>
                    
                    <div id="navbar" className="collapse navbar-collapse">
                        <ul className="nav navbar-nav">
                        {Object.values(this.state.tabs).map(
                            (tabData) => {
                                return (
                                    <NavLink name={tabData.name}
                                        id={tabData.id}
                                        key={tabData.id}
                                        className={"navigation " + tabData.selectedClass}
                                        onClick={() => this.linkClickHandler(tabData.name)}
                                        text={tabData.text}
                                    />
                                )
                            }
                        )}
                        </ul>
                    </div>
                </div>
            </nav>
        );
    }

    linkClickHandler(clickedTab) {
        console.log("click on " + clickedTab);
        const modifiedTabs = this.getTabsWithProperSelectedClass(clickedTab);
        const newState = Object.assign(
            {}, 
            this.state, 
            {tabs: modifiedTabs}
        )
        this.setState(newState);
        this.props.onClick(clickedTab);
    }

    getTabsWithProperSelectedClass(selectedTab) {
        const modifiedTabs = Object.assign({}, this.state.tabs);
        for (const i in modifiedTabs) {
            if (modifiedTabs.hasOwnProperty(i)) {
                if (i === selectedTab) {
                    modifiedTabs[i].selectedClass = 'selected';
                } else {
                    modifiedTabs[i].selectedClass = '';
                }
            }
        }
        return modifiedTabs;
    }
}