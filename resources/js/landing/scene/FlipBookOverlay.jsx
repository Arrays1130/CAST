export default function FlipBookOverlay({ pages }) {
    return (
        <div className="landing-flipbook" data-flipbook-book>
            <div className="landing-flipbook__spine" aria-hidden="true" />
            <div className="landing-flipbook__page-block" aria-hidden="true" />

            <div className="landing-flipbook__leaf landing-flipbook__cover" data-flipbook-cover>
                <div className="landing-flipbook__face landing-flipbook__face--front">
                    <span className="landing-flipbook__eyebrow">CAST manuscript</span>
                    <strong>From first draft<br />to final defense.</strong>
                    <span className="landing-flipbook__cover-mark">C</span>
                </div>
                <div className="landing-flipbook__face landing-flipbook__face--back">
                    <span className="landing-flipbook__folio">Inside cover</span>
                    <p>Every chapter, revision, and adviser decision—kept in one review trail.</p>
                </div>
            </div>

            {pages.map((page, index) => (
                <div
                    key={page.chapter}
                    className="landing-flipbook__leaf landing-flipbook__page"
                    data-flipbook-page={index}
                    style={{ '--page-index': index }}
                >
                    <div className="landing-flipbook__face landing-flipbook__face--front">
                        <span className="landing-flipbook__folio">{String(index + 1).padStart(2, '0')}</span>
                        <span className="landing-flipbook__eyebrow">{page.chapter}</span>
                        <strong>{page.title}</strong>
                        <p>{page.note}</p>
                        <span className="landing-flipbook__status">{page.status}</span>
                        <span className="landing-flipbook__sweep" data-flipbook-sweep aria-hidden="true" />
                        <span className="landing-flipbook__curl" data-flipbook-curl aria-hidden="true" />
                    </div>
                    <div className="landing-flipbook__face landing-flipbook__face--back">
                        <span className="landing-flipbook__folio">{String(index + 2).padStart(2, '0')}</span>
                        <span className="landing-flipbook__eyebrow">Revision trail</span>
                        <strong>{index === 2 ? 'Defense cleared.' : 'Feedback applied.'}</strong>
                        <p>{index === 2
                            ? 'The manuscript is complete and ready to present.'
                            : 'Comments become clear, trackable improvements.'}</p>
                        <div className="landing-flipbook__notes" aria-hidden="true"><i /><i /><i /></div>
                    </div>
                </div>
            ))}

            <div className="landing-flipbook__final">
                <span className="landing-flipbook__folio">Final</span>
                <span className="landing-flipbook__eyebrow">CAST · Defense copy</span>
                <strong>Cleared.<br />Confident.<br />Complete.</strong>
                <span className="landing-flipbook__seal">Approved</span>
            </div>
        </div>
    );
}
